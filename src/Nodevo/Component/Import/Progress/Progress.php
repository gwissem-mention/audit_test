<?php
namespace Nodevo\Component\Import\Progress;

class Progress implements ProgressInterface
{
    protected $currentIndex = 0;
    protected $currentItem;

    protected $success = [];
    protected $errors = [];

    protected $startTime;
    protected $endTime;

    protected $messages = [];
    protected $exceptions;

    public function __construct() //LOGGER
    {
        $this->exceptions = new \SplObjectStorage();
    }

    public function start()
    {
        $this->startTime = microtime(true);
    }

    public function end()
    {
        $this->endTime = microtime(true);
    }

    public function setCurrentIndex($index)
    {
        $this->currentIndex = $index;
    }

    public function setCurrentItem($data)
    {
        $this->currentItem = $data;
    }

    public function addSuccess($context)
    {
        $this->success[] = [
            'index' => $this->currentIndex,
            'context' =>$context
        ];
    }

    public function getSuccessCount()
    {
        return count($this->success);
    }

    public function addMessage($message, $context = null, $type = null, $code = null)
    {
        if (null === $type) {
            $type = 'common';
        }

        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }

        $this->messages[$type][] = [
            'message' => $message,
            'context' => $context !== null ? $context : $this->currentItem,
            'index' => $this->currentIndex,
            'code' => $code
        ];
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function addException(\Exception $e)
    {
        $this->exceptions->attach($e, $this->currentIndex);
        $this->errors[$this->currentIndex] = $e->getMessage();
    }

    public function hasErrors()
    {
        return count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getDuraction()
    {
        return $this->endTime - $this->startTime;
    }

    public function __sleep()
    {
        return [
            'success',
            'errors',
            'messages'
        ];
    }
}
