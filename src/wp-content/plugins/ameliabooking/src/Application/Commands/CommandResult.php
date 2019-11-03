<?php
/**
 * Class for standardizing command results
 */

namespace AmeliaBooking\Application\Commands;

/**
 * Class CommandResult
 *
 * @package AmeliaBooking\Application\Commands
 */
class CommandResult
{
    const RESULT_SUCCESS = 'success';
    const RESULT_ERROR = 'error';
    const RESULT_CONFLICT = 'conflict';

    private $data;
    private $message;

    private $result = self::RESULT_SUCCESS;

    private $attachment = false;
    private $ics = false;
    private $url;

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function hasAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param mixed $attachment
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param mixed $ics
     */
    public function setIcs($ics)
    {
        $this->ics = $ics;
    }

    /**
     * @return mixed
     */
    public function isIcs()
    {
        return $this->ics;
    }
}
