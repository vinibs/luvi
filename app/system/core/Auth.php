<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class Auth {

	public static function doLogin (
		$authData, // [0 => username, 1 => password]
		$redirectLocation = null, 
		$dbTable = 'user'
		) {

		foreach ($authData as $field => $data) {
			$dbField[] = $field;
			$compared[] = $data;
		}

		$formUser = $dbField[0]; // Nome do input referente ao usuário
		$formPass = $dbField[1]; // Nome do input referente à senha

		$result = (new DB)->where($dbField[0].' = ?', $compared[0], $dbTable)->find();

		if (count($result) == 0) {
			$error = [$formUser => msgAuthNoUsername];
			back()->withValues()->withErrors($error);
			return FALSE;

		} else {
			$result = (new DB)->where(
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
				return TRUE;

			} else {				
				// Conta o número de tentativas de login
				if (!isset($_SESSION['LoginTries'][$compared[0]]))
					$_SESSION['LoginTries'][$compared[0]] = 1;
				else
					$_SESSION['LoginTries'][$compared[0]] = $_SESSION['LoginTries'][$compared[0]] + 1;

				session('LastName', $compared[0]);

				$error = [$formPass => msgAuthIncorrectPass];
				back()->withValues()->withErrors($error);
				return FALSE;
			}
		}
	}

	public static function doLogout () {
		unset($_SESSION[SESSION_NAME.'Auth']);
		unset($_SESSION['flash']['inputErrors']);
		unset($_SESSION['flash']['inputValues']);
		unset($_SESSION['LoginTries']);
		unset($_SESSION['LastName']);
	}

	public static function doRegister (
		$userData,  
		$dbTable = 'user'
		) {

		foreach ($userData as $field => $data) {
			$dbField[] = $field;
			$compared[] = $data;
		}

		$formUser = $dbField[0]; // Nome do input referente ao usuário

		$result = (new DB)->where($dbField[0].' = ?', $compared[0], $dbTable)->find();

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
			return FALSE;			
	}

	public static function createAuthSession ($user, $redirectLocation = null) {
		session(SESSION_NAME.'Auth', [
			'LastActivity' => time(), 
			'User' => serialize($user)
		]);

		if($redirectLocation != null)
		    redirect($redirectLocation);
	}

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

	public static function isLogged () {
        if (isset($_SESSION[SESSION_NAME.'Auth']))
			return TRUE;
		else
			return FALSE;
	}

	public static function getLoggedUser () {
		if (isset($_SESSION[SESSION_NAME.'Auth']))
			return unserialize($_SESSION[SESSION_NAME.'Auth']['User']);
		else
			return NULL;
	}

	public static function bindAuth (
		$authData, // [0 => username, 1 => password]
		$dbTable = 'user'
		) {

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
			return TRUE;
		else {
            // Conta o número de tentativas de login
            self::registerTries($compared[0]);

            return FALSE;
        }
	}

	public static function registerTries ($username) {
	    // Registra o número de tentativas de login para o dado nome de usuário
        if (!isset($_SESSION['LoginTries'][$username]))
            $_SESSION['LoginTries'][$username] = 1;
        else
            $_SESSION['LoginTries'][$username] = $_SESSION['LoginTries'][$username] + 1;

        session('LastName', $username);
    }

	public static function countTries ($maxTries = 5) {
		if (isset($_SESSION['LoginTries'][Auth::getLastUsername()]) &&
		 $_SESSION['LoginTries'][Auth::getLastUsername()] >= $maxTries) 
			return TRUE;
		else
			return FALSE;
	}

	public static function hashPassword ($passwordText) {
		return hash('whirlpool', $passwordText);
	}




	// Funções para o sistema de redefinição de senha

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
			$request->setVal(1);
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
				return TRUE;
			else
				return FALSE;

		} else
			return FALSE;
	}

	public static function checkToken ($token, $resetRequestClass) {
		$request = (new $resetRequestClass)->where('token = ? AND val = ?', [$token, 1])->find();

		if (count($request) == 1)
			return TRUE;
		else
			return FALSE;
	}

	public static function resendEmail (
		$email, 
		$userClass, 
		$resetRequestClass,
		$urlValidate,
		$mailContent = null
	) {
		$request = (new $resetRequestClass)->where('email = ? AND val = ?', [$email, 1])->find();
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
			return TRUE;
		else
			return FALSE;

	}

	public static function changePassword (
		$passData, 
		$token, 
		$userClass, 
		$resetRequestClass
	) {
		foreach ($passData as $i => $pass) {
			$index[] = $i;
			$password[] = $pass;
		}

		$valPass = Validation::check($passData, [
			$index[0] => 'required|equal:'.$index[1],
			$index[1] => 'required'
		]);

		if ($valPass) {
			$request = (new $resetRequestClass)->where('token = ? AND val = ?', [$token, 1])->find();
			if (sizeof($request) == 1)
				$request = $request[0];
			else
				return FALSE;

			$request->setVal(0);			
			$request->save();
			$email = $request->getEmail();

			$user = (new $userClass)->where('email = ?', $email)->find()[0];
			$user->setPassword($password[0]);
			if ($user->save())
				return TRUE;
			else {
				$request->setVal(1);
				$request->save();
				return FALSE;
			}
		}
	}
	


	public static function getLastUsername () {
		if (isset($_SESSION['LastName']))
			return $_SESSION['LastName'];
		else
			return null;
	}


    public static function make () {
        return new self;
    }
}