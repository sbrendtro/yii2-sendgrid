<?php
namespace sbrendtro\sendgrid;

use wadeshuler\sendgrid\Message as BaseMessage;

class Message extends BaseMessage
{
    /**
     * @inheritdoc
     */
    public function setFrom($from)
    {
        $this->from = $from;
        if ( ! $this->replyTo )
        {
            $reply = is_array($from) ? $from[0] : $from;
            $this->setReplyTo($reply);
        }
        return $this;
    }
}
