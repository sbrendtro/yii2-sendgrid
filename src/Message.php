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
            $reply = $from;
            if ( is_array($reply) )
            {
                $emails = array_keys($reply);
                $reply = array_shift($emails);
            }
            $this->setReplyTo($reply);
        }
        return $this;
    }
}
