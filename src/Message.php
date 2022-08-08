<?php
namespace sbrendtro\sendgrid;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\base\NotSupportedException;
use yii\mail\BaseMessage;

class Message extends BaseMessage
{
    const LOGNAME = 'SendGrid Mailer';

    /**
     * @inheritdoc
     */
    public $mailer;

    /**
     * @var object SendGrid Mail instance
     */
    private $_sendGridMail;

    /**
     * Holds personalizations for final processing. This holds: to, bcc, cc, substitutions
     *
     * Example: [
     *     'to' => $user->email,
     *     'substitutions' => [
     *         'id' => $user->id,
     *         'username' => $user->username,
     *     ]
     * ]
     *
     * @var array An array of personalizations to be used during processing
     */
    public $personalizations;

    /**
     * @var string The email address to send to. Used in single send mode.
     */
    public $to;

    /**
     * @var string The CC email address to send to. Used in single send mode.
     */
    public $cc;

    /**
     * @var string The BCC email address to send to. Used in single send mode.
     */
    public $bcc;

    /**
     * @var array|string The email address to send from `['from@example.com' => 'Joe Smith']` or `from@example.com`
     */
    public $from;

    /**
     * @var string The email address to reply to. Example: `replyto@example.com`
     */
    public $replyTo;

    /**
     * @var string The subject of the email. Substitutions allowed: `Alerts for -name-`
     */
    public $subject;

    /**
     * @var string The Text content of the email body. Substitutions allowed: `Dear -name-,\nThis is the message..`
     */
    public $textBody;

    /**
     * @var string The HTML content of the email body. Substitutions allowed: `Dear -name-,<br>This is the message..`
     */
    public $htmlBody;

    /**
     * An array of attachments. See SendGrid v3 Mail API Overview - Request Body Parameters
     *
     * Example: [
     *     'file' => '/path/to/the/file.pdf',
     *     'options' => [
     *         'type' => 'application/pdf',
     *         'filename' => 'monthlyreport.pdf',
     *         'disposition' => 'attachment',
     *         'content_id' => 'ii_139db99fdb5c3704',
     *     ]
     * ]
     *
     * @link https://sendgrid.com/docs/API_Reference/Web_API_v3/Mail/index.html#-Request-Body-Parameters
     *
     * @var array
     */
    public $attachments;

    /**
     * @var string Use your SendGrid template. Example: `439b6d66-4408-4ead-83de-5c83c2ee313a`
     */
    public $templateId;

    /**
     * Array of sections located in your Content (Text and HTML).
     * You must include your own tags. Example: `%section1%`
     *
     * Example: [
     *     '%section1%' => 'Substitution Text for Section 1',
     *     '%section2%' => 'Substitution Text for Section 2',
     * ]
     *
     * @var array
     */
    public $sections;

    /**
     * Array of headers to add to the email.
     *
     * Example: [
     *     'X-Test1' => '1',
     *     'X-Test2' => '2',
     *     'X-track-SentById' => $user->id,
     * ]
     *
     * @var array
     */
    public $headers;

    /**
     * Array of SendGrid categories. Max 10 categories per message and 255 chars each.
     *
     * Example: ['May', '2017', 'monthly', 'reports']
     *
     * @var array
     */
    public $categories;

    /**
     * Array of custom arguments.
     *
     * Example: [
     *     'user_id' => '343',
     *     'type' => 'marketing',
     * ]
     *
     * @var array
     */
    public $customArgs;

    /**
     * @var integer Unix timestamp of when you want the email to send. Example: `1443636842`
     */
    public $sendAt;

    /**
     * @var string Valid batch id from SendGrid representing a batch of the same or similar emails.
     */
    public $batchId;

    /**
     * @var string The SendGrid IP Pool to send from. Example: `"23"`
     */
    public $ipPoolName;

    /**
     * @var array
     */
    public $substitutions;

    /**
     * @var null This is not used and left here to satisfy Inheritance
     */
    public $charset;

    /**
     * @return \SendGrid\Mail Object
     */
    public function getSendGridMail()
    {
        if ( ! is_object($this->_sendGridMail) ) {
            $this->_sendGridMail = $this->createSendGridMail();
        }

        return $this->_sendGridMail;
    }

    /**
     * Create SendGrid Mail object
     *
     * @return \SendGrid\Mail
     * @throws \yii\base\InvalidConfigException
     */
    public function createSendGridMail()
    {
        return new \SendGrid\Mail();
    }

    public function addPersonalization($personalization)
    {
        $this->personalizations[] = $personalization;
        return $this;
    }

    public function addSubstitution($key, $val)
    {
        $this->substitutions[$key] = (string) $val;
        return $this;
    }

    public function addHeader($key, $val)
    {
        $this->headers[$key] = (string) $val;
        return $this;
    }

    public function setTemplateId($id)
    {
        $this->templateId = (string) $id;
        return $this;
    }

    public function addSection($key, $val)
    {
        $this->sections[$key] = (string) $val;
        return $this;
    }

    public function addCategory($category)
    {
        $this->categories[] = (string) $category;
        return $this;
    }

    // did I handle this properly down in code?
    public function addCustomArg($key, $val)
    {
        $this->customArgs[$key] = (string) $val;
        return $this;
    }

    public function setSendAt($time)
    {
        $this->sendAt = $time;
        return $this;
    }

    public function setBatchId($id)
    {
        if ( isset($id) && ! empty($id) ) {
            $this->batchId = (string) $id;
        }

        return $this;
    }

    public function setIpPoolName($name)
    {
        $this->ipPoolName = (string) $name;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @inheritdoc
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @inheritdoc
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setHtmlBody($html)
    {
        $this->htmlBody = $html;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTextBody($text)
    {
        $this->textBody = $text;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @inheritdoc
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFrom()
    {
        return $this->from;
    }

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

    /**
     * @inheritdoc
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @inheritdoc
     */
    public function setReplyTo($replyTo)
    {
        $this->replyTo = $replyTo;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * @inheritdoc
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @inheritdoc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attach($fileName, array $options = [])
    {
        $this->attachments[] = ['file' => $fileName, 'options' => $options];
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attachContent($content, array $options = [])
    {
        Yii::warning('attachContent is not implemented', self::LOGNAME);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function embed($fileName, array $options = [])
    {
        Yii::warning('embed is not implemented', self::LOGNAME);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function embedContent($content, array $options = [])
    {
        Yii::warning('embedContent is not implemented', self::LOGNAME);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toString()
    {
        return json_encode($this->buildMessage(), JSON_PRETTY_PRINT);
    }

    /**
     * @return array Get array of errors
     */
    public function getErrors()
    {
        return $this->mailer->getErrors();
    }

    /**
     * Builds the SendGrid message payload.
     *
     * @return \SendGrid\Mail instance or false on error
     */
    public function buildMessage()
    {
        if ( isset($this->from, $this->subject) && ( (isset($this->textBody) && !empty($this->textBody)) || (isset($this->htmlBody) && !empty($this->htmlBody)) ) )
        {
            $batchMode = false;
            if ( isset($this->personalizations) && is_array($this->personalizations) && !empty($this->personalizations) ) {
                $batchMode = true;
                if ( isset($this->to) || isset($this->bcc) || isset($this->cc) ) {
                    Yii::warning('To, BCC, and CC are ignored when using personalizations!');
                }
            }

            // To, BCC, CC, and substitutions are all are wrapped in Personalization objects
            // Personalization objects can also have their own subject, headers, send_at, etc.
            // that take priority over the global sending options
            if ( $batchMode )
            {
                // Batch Send Mode
                foreach ($this->personalizations as $envelope)
                {
                    if ( ! isset($envelope['to']) || empty($envelope['to']) )
                    {

                        Yii::error('personalizations missing "to". Skipping!', self::LOGNAME);
                        return false;

                    } else {

                        $personalization = new \SendGrid\Personalization();

                        if ( is_array($envelope['to']) ) {
                            foreach ($envelope['to'] as $key => $val) {
                                if ( is_int($key) ) {
                                    // `[0 => email]`
                                    $personalization->addTo( new \SendGrid\Email(null, $val) );
                                } else {
                                    // `[email => name]`
                                    $personalization->addTo( new \SendGrid\Email($val, $key) );
                                }
                            }
                        } else {
                            $personalization->addTo( new \SendGrid\Email(null, $envelope['to']) );
                        }

                        if ( isset($envelope['cc']) ) {
                            $personalization->addCc( new \SendGrid\Email(null, $envelope['cc']) );
                        }

                        if ( isset($envelope['bcc']) ) {
                            $personalization->addBcc( new \SendGrid\Email(null, $envelope['bcc']) );
                        }

                        if ( isset($envelope['subject']) ) {
                            $personalization->setSubject( (string) $envelope['subject'] );
                        }

                        if ( isset($envelope['headers']) && is_array($envelope['headers']) ) {
                            foreach ( $envelope['headers'] as $key => $val ) {
                                $personalization->addHeader((string) $key, (string) $val);
                            }
                        }

                        if ( isset($envelope['substitutions']) && is_array($envelope['substitutions']) ) {
                            foreach ( $envelope['substitutions'] as $key => $val ) {
                                $personalization->addSubstitution((string) $key, (string) $val);
                            }
                        }

                        if ( isset($envelope['custom_args']) && is_array($envelope['custom_args']) ) {
                            foreach ($envelope['custom_args'] as $key => $val) {
                                $personalization->addCustomArg((string) $key, (string) $val);
                            }
                        }

                        if ( isset($envelope['send_at']) && is_int($envelope['send_at']) ) {
                            $personalization->setSendAt($envelope['send_at']);
                        }

                        $this->getSendGridMail()->addPersonalization($personalization);

                    }

                }

            } else {

                // Single Send Mode
                $personalization = new \SendGrid\Personalization();

                if ( is_array($this->to) ) {
                    foreach ($this->to as $key => $val) {
                        if ( is_int($key) ) {
                            // `[0 => email]`
                            $personalization->addTo( new \SendGrid\Email(null, $val) );
                        } else {
                            // `[email => name]`
                            $personalization->addTo( new \SendGrid\Email($val, $key) );
                        }
                    }
                } else {
                    $personalization->addTo( new \SendGrid\Email(null, $this->to) );
                }

                if ( isset($this->bcc) ) {
                    $personalization->addBcc( new \SendGrid\Email(null, $this->bcc) );
                }

                if ( isset($this->cc) ) {
                    $personalization->addCc( new \SendGrid\Email(null, $this->cc) );
                }

                if ( isset($this->substitutions) && is_array($this->substitutions) ) {
                    foreach ($this->substitutions as $key => $val) {
                        $personalization->addSubstitution((string) $key, (string) $val);
                    }
                }

                $this->getSendGridMail()->addPersonalization($personalization);

            }

            if ( is_array($this->from) ) {
                if ( is_numeric(key($this->from)) ) {
                    $this->getSendGridMail()->setFrom( new \SendGrid\Email(null, $this->from[0]) );
                } else {
                    reset($this->from);     // reset pointer to beginning. Necessary when using current() and key()
                    $this->getSendGridMail()->setFrom( new \SendGrid\Email(current($this->from), key($this->from)) );
                }
            } else {
                $this->getSendGridMail()->setFrom( new \SendGrid\Email(null, $this->from) );
            }

            // SendGrid-PHP library only supports string email
            // however v3 Web API supports name & email
            // @issue https://github.com/sendgrid/sendgrid-php/issues/390
            if ( is_string($this->replyTo) ) {
                $this->getSendGridMail()->setReplyTo( new \SendGrid\ReplyTo($this->replyTo) );
            } else {
                Yii::warning('ReplyTo must be a string and was ignored!');
            }

            $this->getSendGridMail()->setSubject($this->subject);

            if ( isset($this->textBody) && !empty($this->textBody) )
            {
                $content = new \SendGrid\Content('text/plain', $this->textBody);
                $this->getSendGridMail()->addContent($content);
            } else {
                // According to RFC 1341, section 7.2, plain text content needs to come
                // before any HTML content. Since Yii first adds HTML content in some
                // circumstances, SendGrid refuses to send the message. We therefore
                // always prepend plain text content to the message.
                $content = new \SendGrid\Content('text/plain', ' ');
                $this->getSendGridMail()->addContent($content);
            }

            if ( isset($this->htmlBody) && !empty($this->htmlBody) )
            {
                $content = new \SendGrid\Content('text/html', $this->htmlBody);
                $this->getSendGridMail()->addContent($content);
            }

            if ( isset($this->attachments) && is_array($this->attachments) ) {
                foreach ($this->attachments as $attachment) {
                    $file = $attachment['file'];
                    if ( file_exists($file) )
                    {
                        $content = base64_encode(file_get_contents($file));

                        $sgAttachment = new \SendGrid\Attachment();
                        $sgAttachment->setContent($content);

                        if ( isset($attachment['options']) && is_array($attachment['options']) && !empty($attachment['options']) )
                        {
                            $options = $attachment['options'];

                            if ( isset($options['type']) ) {
                                $sgAttachment->setType($options['type']);
                            }

                            if ( isset($options['filename']) && ! empty($options['filename']) ) {
                                $sgAttachment->setFilename($options['filename']);
                            } else {
                                $sgAttachment->setFilename(basename($file));
                            }

                            if ( isset($options['disposition']) ) {
                                $sgAttachment->setDisposition($options['disposition']);
                            }

                            if ( isset($options['content_id']) ) {
                                $sgAttachment->setContentId($options['content_id']);
                            }
                        } else {
                            // filename is required
                            $sgAttachment->setFilename(basename($file));
                        }

                        $this->getSendGridMail()->addAttachment($sgAttachment);

                    } else {
                        Yii::warning('Attachment file does not exist: ' . $file, self::LOGNAME);
                    }

                }
            }

            if ( isset($this->templateId) && !empty($this->templateId) ) {
                $this->getSendGridMail()->setTemplateId($this->templateId);
            }

            // must include own tags: Example: `-header-`
            if ( isset($this->sections) && is_array($this->sections) ) {
                foreach ($this->sections as $key => $val) {
                    $this->getSendGridMail()->addSection($key, $val);
                }
            }

            if ( isset($this->headers) && is_array($this->headers) ) {
                foreach ($this->headers as $key => $val) {
                    $this->getSendGridMail()->addHeader($key, $val);
                }
            }

            if ( isset($this->categories) && is_array($this->categories) ) {
                foreach ($this->categories as $category) {
                    $this->getSendGridMail()->addCategory($category);
                }
            }

            if ( isset($this->customArgs) && is_array($this->customArgs) ) {
                foreach ($this->customArgs as $key => $val) {
                    $this->getSendGridMail()->addCustomArg((string) $key, (string) $val);
                }
            }

            if ( isset($this->sendAt) && is_int($this->sendAt) ) {
                $this->getSendGridMail()->setSendAt($this->sendAt);
            }

            if ( isset($this->batchId) && !empty($this->batchId) ) {
                $this->getSendGridMail()->setBatchID($this->batchId);
            }

            // @todo asm

            if ( isset($this->ipPoolName) && !empty($this->ipPoolName) ) {
                $this->getSendGridMail()->setIpPoolName($this->ipPoolName);
            }

            // @todo mail_settings
            // @todo tracking_settings

            return $this->getSendGridMail();

        } else {

            Yii::error('From, subject, and message text or html are required for mailing!', self::LOGNAME);
            return false;

        }

        return false;
    }

}
