<?php

namespace CleaRest\Api\Data;

/**
 * Use this class as a parameter for your service to sign to the API a uploaded file is expected for that parameter
 */
class UploadedFile
{
    /**
    * Original name of file
    *
    * @var string
    */
    private $name;

    /**
     * File type
     *
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $tmpName;

    /**
     * @var int
     */
    private $error;

    /**
     * @var int
     */
    private $size;

    private final function __construct($name = null, $type = null, $tmpName = null, $error = 0, $size = 0)
    {
        $this->name    = $name;
        $this->type    = $type;
        $this->tmpName = $tmpName;
        $this->error   = $error;
        $this->size    = $size;
    }

    /**
     * Original name of file
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * File type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Temporary name of uploaded file
     *
     * @return string
     */
    public function getTemporaryPath()
    {
        return $this->tmpName;
    }

    /**
     * Error code, if happened
     *
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * File size in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Moves current uploaded file to new directory.
     * If parameter $newName is not given, current name is used
     *
     * @param string $newPath
     * @param string $newName
     */
    public function moveTo($newPath, $newName = null)
    {
        if ($newName === null) {
            $newName = $this->getName();
        }
        move_uploaded_file($this->getTemporaryPath(), $newPath . DIRECTORY_SEPARATOR . $newName);
    }

    /**
     * Returns file or array of files defined for a field name
     * @param $name
     * @return UploadedFile[]|UploadedFile
     */
    public static function getFromField($name)
    {
        if (!isset($_FILES[$name])) {
            return null;
        }

        $data = $_FILES[$name];
        if (!is_array($data['name'])) {
            return new self(
                $data['name'],
                $data['type'],
                $data['tmp_name'],
                $data['error'],
                $data['size']
            );
        } else {
            $files = [];
            foreach ($data['name'] as $key => $value) {
                $files[$key] = new self(
                    $data['name'][$key],
                    $data['type'][$key],
                    $data['tmp_name'][$key],
                    $data['error'][$key],
                    $data['size'][$key]
                );
            }
            return $files;
        }
    }
}

