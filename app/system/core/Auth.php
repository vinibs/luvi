<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class Auth
 *
 * Manages the authentication system of the framework
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */
class Auth
{

    /**
     * @param array $authData
     * @param string|null $redirectLocation
     * @param string $dbTable
     * @return bool
     *
     * Realiza o login, seguindo alguns padrões de banco de dados e nomenclatura
     */
	public static function doLogin (
		$authData, // [0 => username, 1 => password]
		$redirectLocation = null, 
		$dbTable = 'user'
		) {

	    $dbField = array();
	    $compared = array();

		foreach ($authData as $field => $data) {
			$dbField[] = $field;
			$compared[] = $data;
		}

		$formUser = $dbField[0]; // Nome do input referente ao usuário
		$formPass = $dbField[1]; // Nome do input referente à senha

		$result = DB::make()->where($dbField[0].' = ?', $compared[0], $dbTable)->find();

		if (count($result) == 0) {
			$error = [$formUser => msgAuthNoUsername];
			back()->withValues()->withErrors($error);
			return false;

		} else {
			$result = DB::make()->where(
					$dbField[0].' = ? AND '.$dbField[1].' = ?', 
					array(
						$compared[0],
						self::hashPassword($compared[1])
					),
					$dbTable
				)->find();
			
			if (count($result) == 1) {
				if (isset($_SESSION['LoginTries'][$compared[0]]))
					unset($_SESSION['LoginTries'][$compared[0]]);

				$idFunc = 'get'.ucfirst($result[0]->primarykey);
				$userFunc = 'get'.ucfirst($dbField[0]);

				self::createAuthSession(
					array(
						$result[0]->primarykey => $result[0]->$idFunc(),
						$dbField => $result[0]->$userFunc()
					), 
					$redirectLocation
				);
				return true;

			} else {				
				// Conta o número de tentativas de login
				if (!isset($_SESSION['LoginTries'][$compared[0]]))
					$_SESSION['LoginTries'][$compared[0]] = 1;
				else
					$_SESSION['LoginTries'][$compared[0]] = $_SESSION['LoginTries'][$compared[0]] + 1;

				session('LastName', $compared[0]);

				$error = [$formPass => msgAuthIncorrectPass];
				back()->withValues()->withErrors($error);
				return false;
			}
		}
	}


    /**
     * @return void
     *
     * Realiza o logout do sistema, com base na estrutura padrão de login do framework
     */
	public static function doLogout () {
		unset($_SESSION[SESSION_NAME.'Auth']);
		unset($_SESSION['flash']['inputErrors']);
		unset($_SESSION['flash']['inputValues']);
		unset($_SESSION['LoginTries']);
		unset($_SESSION['LastName']);
	}


    /**
     * @param array $userData
     * @param string $dbTable
     * @return bool|object
     *
     * Realiza o registro de um usuário, seguindo alguns padrões de banco de dados e nomenclatura
     */
	public static function doRegister (
		$userData,  
		$dbTable = 'user'
		) {

        $dbField = array();
        $compared = array();

        foreach ($userData as $field => $data) {
			$dbField[] = $field;
			$compared[] = $data;
		}

		$formUser = $dbField[0]; // Nome do input referente ao usuário

		$result = DB::make()->where($dbField[0].' = ?', $compared[0], $dbTable)->find();

		if (count($result) == 0) {
			$user = new $dbTable;

			foreach ($dbField as $i => $field) {
				$func = 'set'.ucfirst($field);
				$user->$func($compared[$i]);
			}

			$user->save();
			return $user;

		} else
			$error = [$formUser => msgAuthUsernameUnavailable];
			back()->withValues()->withErrors($error);
			return false;
	}


    /**
     * @param string|array|object $user
     * @param string|null $redirectLocation
     *
     * Realiza a criação da sessão do usuário e, se passado por parâmetro, redireciona o usuário
     */
	public static function createAuthSession ($user, $redirectLocation = null) {
		session(SESSION_NAME.'Auth', [
			'LastActivity' => time(), 
			'User' => serialize($user)
		]);

		if($redirectLocation != null)
		    redirect($redirectLocation);
	}


    /**
     * @param string|null $redirectNoLogin
     * @param string|null $redirectTimeout
     * @return void
     *
     * Define um trecho como restrito a usuários logados e, se passado, redireciona o usuário quando não houver
     * login ou tiver sido passado o tempo de timeout
     */
	public static function setRestricted ($redirectNoLogin = null, $redirectTimeout = null) {
		if (!self::isLogged()) {
			// Se não estiver logado 			
			redirect($redirectNoLogin);
			die;
		} else if (SESSION_ACTIVITY_TIME > 0 &&
			(time() - session(SESSION_NAME.'Auth')['LastActivity']) > SESSION_ACTIVITY_TIME * 60) {
			// Se tiver passado o tempo da sessão inativa faz logout
			self::doLogout();
			redirect($redirectTimeout);
			die;
		} else
			// Se não tiver passado o tempo da sessão inativa, redefine o tempo
			$_SESSION[SESSION_NAME.'Auth']['LastActivity'] = time();
	}


    /**
     * @return bool
     *
     * Verifica se o usuário está logado, seguindo o padrão de autenticação do framework
     */
	public static function isLogged () {
        if (isset($_SESSION[SESSION_NAME.'Auth']))
			return true;
		else
			return false;
	}


    /**
     * @return null
     *
     * Obtém o usuário atualmente logado no sistema, a partir do padrão do framework
     */
	public static function getLoggedUser () {
		if (isset($_SESSION[SESSION_NAME.'Auth']))
			return unserialize($_SESSION[SESSION_NAME.'Auth']['User']);
        else
			return null;
	}


    /**
     * @param array $authData
     * @param string $dbTable
     * @return bool
     *
     * Verifica a autenticação dos dados do usuário passados por parâmetro
     */
	public static function bindAuth (
		$authData, // [0 => username, 1 => password]
		$dbTable = 'user'
		) {

        $dbField = array();
        $compared = array();

        foreach ($authData as $field => $data) {
			$dbField[] = $field;
			$compared[] = $data;
		}

		$result = (new DB)->where(
				$dbField[0].' = ? AND '.$dbField[1].' = ?', 
				array(
					$compared[0],
					self::hashPassword($compared[1])
				),
				$dbTable
			)->find();
		
		if (count($result) == 1)
			return true;
		else {
            // Conta o número de tentativas de login
            self::registerTries($compared[0]);

            return true;
        }
	}


    /**
     * @param string $username
     * @param string $quant
     *
     * Registra o número de tentativas incorretas de login para um dado nome de usuário
     */
	public static function registerTries ($username, $quant = '') {
	    // Registra o número de tentativas de login para o dado nome de usuário
        if (!isset($_SESSION['LoginTries'][$username]))
            $_SESSION['LoginTries'][$username] = ($quant !== '') ? $quant : 1;
        else
            $_SESSION['LoginTries'][$username] = ($quant !== '') ? $quant : $_SESSION['LoginTries'][$username] + 1;

        session('LastName', $username);
    }


    /**
     * @param int $maxTries
     * @return bool
     *
     * Verifica o número de tentativas incorretas de login e indica se foi passado o limite de tentativas
     */
	public static function countTries ($maxTries = 5) {
		if (isset($_SESSION['LoginTries'][Auth::getLastUsername()]) &&
		    $_SESSION['LoginTries'][Auth::getLastUsername()] >= $maxTries)
			return true;
		else
			return false;
	}


    /**
     * @param string $passwordText
     * @return string
     *
     * Realiza o hash do texto passado por parâmetro, normalmente senha
     */
	public static function hashPassword ($passwordText) {
		return hash('whirlpool', $passwordText);
	}




	// Funções para o sistema de redefinição de senha

    /**
     * @param string $email
     * @param string $userClass
     * @param string $resetRequestClass
     * @param string $urlValidate
     * @param string|null $mailContent
     * @return bool
     *
     * Realiza a solicitação de redefinição de senha, seguindo o padrão de banco de dados e nomenclatura do framework
     */
	public static function requestPassReset (
		$email, 
		$userClass,
		$resetRequestClass,
		$urlValidate,
		$mailContent = null
	) {
		$user = (new $userClass)->where('email = ?', $email)->find();
		if (count($user) == 1) {
			$request = new $resetRequestClass;
			do {
				$token = md5(uniqid(rand(), true));				
			} while (count($request->where('token = ?', $token)->find()) > 0);

			$request->setEmail($email);
			$request->setToken($token);
			$request->setValid(1);
			$request->save();

			// Remove as barras do início e do fim da URL caso seja necessário
			if (substr($urlValidate, 0, 1) == '/')
				$urlValidate = substr($urlValidate, 1);

			if (substr($urlValidate, -1) == '/')
				$urlValidate = substr($urlValidate, 0, strlen($urlValidate) - 1);

			$link = $_SERVER['HTTP_HOST'].SYSROOT.'/'.$urlValidate.'/'.$token;

			if (!is_null($mailContent))
				$content = $mailContent.'<br><br>'.'<a href="'.$link.'">'.$link.'</a>';
			else
				$content = '<a href="'.$link.'">'.$link.'</a>';


			$mail = new Mail;
			$mail->setTo($email);
			$mail->setSubject(('Passord Reset'));
			$mail->setContent($content);
			if($mail->send())
				return true;
			else
				return false;

		} else
			return false;
	}


    /**
     * @param string $token
     * @param string $resetRequestClass
     * @return bool
     *
     * Verifica a autenticação do token enviado por email ao usuário, seguindo padrões de BD e nomenclatura
     */
	public static function checkToken ($token, $resetRequestClass) {
		$request = (new $resetRequestClass)->where('token = ? AND valid = ?', [$token, 1])->find();

		if (count($request) == 1)
			return true;
		else
			return false;
	}


    /**
     * @param string $email
     * @param string $resetRequestClass
     * @param string $urlValidate
     * @param string|null $mailContent
     * @return bool
     *
     * Realiza o reenvio do email de redefinição de senha, com base no padrão de BD e nomenclatura
     */
	public static function resendEmail (
		$email,
		$resetRequestClass,
		$urlValidate,
		$mailContent = null
	) {
		$request = (new $resetRequestClass)->where('email = ? AND valid = ?', [$email, 1])->find();
		$request = $request[sizeof($request) - 1]; // Pega o último, caso haja mais de um
		$token = $request->getToken();

		// Remove as barras do início e do fim da URL caso seja necessário
		if (substr($urlValidate, 0, 1) == '/')
			$urlValidate = substr($urlValidate, 1);

		if (substr($urlValidate, -1) == '/')
			$urlValidate = substr($urlValidate, 0, strlen($urlValidate) - 1);

		$link = $_SERVER['HTTP_HOST'].SYSROOT.'/'.$urlValidate.'/'.$token;

		if (!is_null($mailContent))
			$content = $mailContent.'<br><br>'.'<a href="'.$link.'">'.$link.'</a>';
		else
			$content = '<a href="'.$link.'">'.$link.'</a>';


		$mail = new Mail;
		$mail->setTo($email);
		$mail->setSubject(('Passord Reset'));
		$mail->setContent($content);
		if($mail->send())
			return true;
		else
			return false;

	}


    /**
     * @param array $passData
     * @param string $token
     * @param string $userClass
     * @param string $resetRequestClass
     * @return bool
     *
     * Realiza a alteração da senha do usuário propriamente dita
     */
	public static function changePassword (
		$passData, 
		$token, 
		$userClass, 
		$resetRequestClass
	) {

	    $index = array();
	    $password = array();

		foreach ($passData as $i => $pass) {
			$index[] = $i;
			$password[] = $pass;
		}

		$valPass = Validation::check($passData, [
			$index[0] => 'required|equal:'.$index[1],
			$index[1] => 'required'
		]);

		if ($valPass) {
			$request = (new $resetRequestClass)->where('token = ? AND valid = ?', [$token, 1])->find();
			if (sizeof($request) == 1)
				$request = $request[0];
			else
				return false;

			$request->setValid(0);
			$request->save();
			$email = $request->getEmail();

			$user = (new $userClass)->where('email = ?', $email)->find()[0];
			$user->setPassword($password[0]);
			if ($user->save())
				return true;
			else {
				$request->setValid(1);
				$request->save();
				return false;
			}
		}
		else
		    return false;
	}


    /**
     * @return null|string
     *
     * Obtém o último nome de usuário usado em tentativa de login
     */
	public static function getLastUsername () {
		if (isset($_SESSION['LastName']))
			return $_SESSION['LastName'];
		else
			return null;
	}


    /**
     * @return Auth
     *
     * Cria uma instância da classe
     */
    public static function make () {
        return new self;
    }
}