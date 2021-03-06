<?php

/*
 * The MIT License
 *
 * Copyright 2020 Maxim Eltratov <Maxim.Eltratov@yandex.ru>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MxmBlog\Model;

use ArrayAccess;
use Laminas\Db\TableGateway\TableGateway;
use MxmBlog\Exception\RecordNotFoundBlogException;
use MxmBlog\Exception\InvalidArgumentBlogException;
use Laminas\Validator\StaticValidator;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter\DbTableGateway;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Validator\Db\NoRecordExists;
use Laminas\Validator\ValidatorInterface;
use Laminas\Filter\StaticFilter;
use Laminas\Hydrator\HydratorInterface;
use Laminas\Tag\ItemList;

class PostManager implements PostManagerInterface {

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $postTableGateway;

    /**
     * @var Laminas\Db\TableGateway\TableGateway
     */
    protected $tagPostTableGateway;

    /**
     * @param TableGateway $postTableGateway
     * @param TableGateway $tagPostTableGateway
     */
    public function __construct(
            TableGateway $postTableGateway,
            TableGateway $tagPostTableGateway
    ) {
        $this->postTableGateway = $postTableGateway;
        $this->tagPostTableGateway = $tagPostTableGateway;
    }

    /**
     * {@see PostManagerInterface}
     */
    public function insertPost(PostInterface $post) {
        $postHydrator = $this->postTableGateway->getResultSetPrototype()->getHydrator();
        $postArray = $postHydrator->extract($post);
        unset($postArray['id']);
        unset($postArray['tags']);
        $this->postTableGateway->insert($postArray);
        $newId = $this->postTableGateway->getLastInsertValue();
        if (!$newId) {
            throw new DataBaseErrorBlogException("Insert operation failed");
        }

        $this->saveTagsAndTagPostAssociations($newId, $post->getTags());

        $resultSet = $this->postTableGateway->select(['id' => $newId]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Insert operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * {@see PostManagerInterface}
     */
    public function updatePost(PostInterface $post) {
        $postHydrator = $this->postTableGateway->getResultSetPrototype()->getHydrator();
        $postArray = $postHydrator->extract($post);
        unset($postArray['id']);
        unset($postArray['tags']);
        $this->postTableGateway->update($postArray, ['id = ?' => $post->getId()]);

        $this->deletePostAssociationWithTags($post->getId());
        $this->saveTagsAndTagPostAssociations($post->getId(), $post->getTags());

        $resultSet = $this->postTableGateway->select(['id' => $post->getId()]);
        if (0 === count($resultSet)) {
            throw new DataBaseErrorBlogException("Update operation failed or did not result in new row.");
        }

        return $resultSet->current();
    }

    /**
     * Удаляет связи поста с тегами (article_id - tag_id)
     * @param string $id
     *
     * @return Affected Rows
     */
    private function deletePostAssociationWithTags($id) {
        return $this->tagPostTableGateway->delete(['article_id' => $id]);
    }

    /**
     * Удаляет связи постов с тегами (article_id - tag_id)
     * @param array $ids
     *
     * @return Affected Rows
     */
    private function deletePostAssociationsWithTags($ids) {
        $sql = $this->tagPostTableGateway->getSql();
        $delete = $sql->delete();
        $delete->where->in('article_id', $ids);

        return $this->tagPostTableGateway->deleteWith($delete);
    }

    /**
     * Сохранить теги и связи тег-статья в базе.
     *
     * @param ArrayAccess Array|ArrayObject with TagInterface tags
     *
     * @return $this
     */
    private function saveTagsAndTagPostAssociations(string $postId, ArrayAccess $tags) {
        for ($offset = 0, $countTags = count($tags); $offset < $countTags; $offset++) {
            $tagId = $tags[$offset]->getId();
            $this->saveTagPostAssociation($tagId, $postId);
        }

        return $this;
    }

    /**
     * Сохранить одну связь "пост-тег" в таблицу articles_tags.
     * @param int|string $tagId tag id
     * @param int|string $postId post id
     *
     * @return $lastInsertId
     * @throws InvalidArgumentBlogException
     * @throws DataBaseErrorBlogException
     */
    private function saveTagPostAssociation($tagId, $postId) {
        $tagId = StaticFilter::execute($tagId, 'Digits');
        $postId = StaticFilter::execute($postId, 'Digits');

        if (empty($tagId) or empty($postId)) {
            throw new InvalidArgumentBlogException("Cannot save tag-post association: Empty param given: tag ID:{$tagId} or post ID:{$postId}");
        }

        $this->tagPostTableGateway->insert([
            'article_id' => $postId,
            'tag_id' => $tagId,
        ]);

        return $this->tagPostTableGateway->getLastInsertValue();
    }

    /**
     * {@see PostManagerInterface}
     */
    public function deletePost(PostInterface $post) {
        $this->deletePostAssociationWithTags($post->getId());
        return $this->postTableGateway->delete(['id' => $post->getId()]);
    }

    /**
     * {@see PostManagerInterface}
     */
    public function deletePosts($posts) {
        if ($posts instanceof Paginator) {
            $posts = iterator_to_array($posts->setItemCountPerPage(-1));
        }

        if (!is_array($posts)) {
            throw new InvalidArgumentBlogException(sprintf(
                            'The data must be array; received "%s"',
                            (is_object($posts) ? get_class($posts) : gettype($posts))
            ));
        }

        if (empty($posts)) {

            return 0;
        }

        $func = function ($value) {
            if (is_string($value)) {
                return $value;
            } elseif ($value instanceof PostInterface) {
                return $value->getId();
            } else {
                throw new InvalidArgumentBlogException(sprintf(
                                'Invalid value in data array detected, value must be a string or instance of PostInterface, %s given.',
                                (is_object($value) ? get_class($value) : gettype($value))
                ));
            }
        };

        $postIds = array_map($func, $posts);

        $sql = $this->postTableGateway->getSql();
        $delete = $sql->delete();
        $delete->where->in('id', $postIds);
        $result = $this->postTableGateway->deleteWith($delete);
        if (0 !== $result) {
            $this->deletePostAssociationsWithTags($postIds);
        }

        return $result;
    }

}
