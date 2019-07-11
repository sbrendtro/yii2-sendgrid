<?php
namespace sbrendtro\sendgrid;

use Yii;
use wadeshuler\sendgrid\Mailer as BaseMailer;

class Mailer extends BaseMailer
{
    public $messageClass = 'sbrendtro\sendgrid\Message';
}
