<?php
class contactMessage {

    private $messageId;
    private $subject;
    private $messageBody;
    private $sentDate;
    private $status;
    private $customerId; // relation
    
    public function getMessageId()   { return $this->messageId; }
public function getSubject()     { return $this->subject; }
public function getMessageBody() { return $this->messageBody; }
public function getSentDate()    { return $this->sentDate; }
public function getStatus()      { return $this->status; }
public function getCustomerId()  { return $this->customerId; }

    public function __construct($messageId, $subject, $messageBody, $sentDate, $customerId) {
        $this->messageId = $messageId;
        $this->subject = $subject;
        $this->messageBody = $messageBody;
        $this->sentDate = $sentDate;
        $this->status = "unread";
        $this->customerId = $customerId;
    }

    public function send(){
        // logic (save message)
    }

    public function markAsRead(){
        $this->status = "read";
    }
}
?>