<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

class Themes extends Model {

    private $name;
    private $content;
    private $created;
    private $last_modified;
    private $enabled;

    /**
     * @return Model\ResultsetInterface|Themes
     */
    public static function getThemes() {
        return self::query()
            ->execute();
    }

    /**
     * @param $id
     * @return bool|ModelInterface|Themes
     */
    public static function getTheme($id) {
        return self::query()
            ->conditions("id = :id:")

            ->bind([
                'id' => $id
            ])->execute()->getFirst();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Themes
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     * @return Themes
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     * @return Themes
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->last_modified;
    }

    /**
     * @param mixed $last_modified
     * @return Themes
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     * @return Themes
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }



}