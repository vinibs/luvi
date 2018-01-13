<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class Mail
 *
 * Offers resources to send emails from the system
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */
class Mail {

    /**
     * @var string $from
     * @var string $fromName
     * @var string $to
     * @var string $subject
     * @var string $content
     */
	private $from;
	private $fromName;
	private $to;
	private $subject;
	private $content;


    /**
     * Mail constructor.
     *
     * Inclui o arquivo do PHPMailer para utilizá-lo
     */
	public function __construct () {
		// Inclui o PHPMailer e tenta simplificar seu uso
		load('/app/system/lib/PHPMailer/PHPMailerAutoload.php');
	}


    /**
     * @param string $from
     * @return Mail
     *
     * Define o remetente do email
     */
	public function setFrom ($from) {
		$this->from = $from;
		return $this;
	}


    /**
     * @param string $fromName
     * @return Mail
     *
     * Define o nome do remetente do email
     */
	public function setFromName ($fromName) {
		$this->fromName = $fromName;
		return $this;
	}


    /**
     * @param string $to
     * @return Mail
     *
     * Define o destinatário do email
     */
	public function setTo ($to) {
		$this->to = $to;
		return $this;
	}


    /**
     * @param string $subject
     * @return Mail
     *
     * Define o assunto do email
     */
	public function setSubject ($subject) {
		$this->subject = $subject;
		return $this;
	}


    /**
     * @param string $content
     * @return Mail
     *
     * Define o conteúdo do email
     */
	public function setContent ($content) {
		$this->content = $content;
		return $this;
	}


    /**
     * @return string
     *
     * Obtém o remetente do email
     */
	public function getFrom () {
		return $this->from;
	}


    /**
     * @return string
     *
     * Obtém o nome do remetente do email
     */
	public function getFromName () {
		return $this->fromName;
	}


    /**
     * @return string
     *
     * Obtém o destinatário do email
     */
	public function getTo () {
		return $this->to;
	}


    /**
     * @return string
     *
     * Obtém o assunto do email
     */
	public function getSubject () {
		return $this->subject;
	}


    /**
     * @return string
     *
     * Obtém o conteúdo do email
     */
	public function getContent () {
		return $this->content;
	}


    /**
     * @return bool
     *
     * Realiza o envio do email propriamente dito
     */
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


    /**
     * @return Mail
     *
     * Cria uma instância da classe
     */
    public static function make () {
        return new self;
    }
}