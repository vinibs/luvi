<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class Mail {
	private $from;
	private $fromName;
	private $to;
	private $subject;
	private $content;

	public function __construct () {
		// Inclui o PHPMailer e tenta simplificar seu uso
		load('/app/system/lib/PHPMailer/PHPMailerAutoload.php');
	}


	public function setFrom ($from) {
		$this->from = $from;
		return $this;
	}

	public function setFromName ($fromName) {
		$this->fromName = $fromName;
		return $this;
	}

	public function setTo ($to) {
		$this->to = $to;
		return $this;
	}

	public function setSubject ($subject) {
		$this->subject = $subject;
		return $this;
	}

	public function setContent ($content) {
		$this->content = $content;
		return $this;
	}


	public function getFrom () {
		return $this->from;
	}

	public function getFromName () {
		return $this->fromName;
	}

	public function getTo () {
		return $this->to;
	}

	public function getSubject () {
		return $this->subject;
	}

	public function getContent () {
		return $this->content;
	}

	public function send () {
		// Resume o uso da classe PHPMailer usando as constantes do sistema
		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = SMTP_HOST;
		$mail->SMTPAuth = SMTP_AUTH;
		$mail->Username = SMTP_USER;
		$mail->Password = SMTP_PASS;
		$mail->SMTPSecure = SMTP_SECURE;
		$mail->Port = SMTP_PORT;
		$mail->IsHTML(true);  

		$mail->setFrom($this->from, $this->fromName);
		$mail->addAddress($this->to);

		$mail->Subject = $this->subject;
		$mail->Body = $this->content;

		return $mail->send();
	}
}