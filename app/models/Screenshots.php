<?php
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

class Screenshots extends Model {

    private $server_id;
    private $images;
    private $owner_id;

    /**
     * @param $server_id
     * @param $owner_id
     * @return bool|ModelInterface|Screenshots
     */
    public static function getScreenshots($server_id, $owner_id) {
        return self::query()
            ->conditions("server_id = :id: AND owner_id = :oid:")
            ->bind([
                'id' => $server_id,
                'oid' => $owner_id
            ])->execute()->getFirst();
    }

    /**
     * @return mixed
     */
    public function getServerId() {
        return $this->server_id;
    }

    /**
     * @param mixed $server_id
     * @return Screenshots
     */
    public function setServerId($server_id) {
        $this->server_id = $server_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getImages() {
        return json_decode($this->images, true);
    }

    public function setImages($images) {
        $this->images = $images;
        return $this;
    }

    public function removeImage($image) {
        $images = $this->getImages();
        $new_images = [];

        foreach ($images as $img) {
            if ($img == $image) {
                continue;
            }
            $new_images[] = $image;
        }

        $this->setImages(json_encode($new_images, JSON_UNESCAPED_SLASHES));
    }

    public function addImage($image) {
        $this->images[] = $image;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * @param mixed $owner_id
     * @return Screenshots
     */
    public function setOwnerId($owner_id)
    {
        $this->owner_id = $owner_id;
        return $this;
    }




}