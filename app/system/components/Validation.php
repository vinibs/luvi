<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class Validation {

	public static function check (array $data, array $rules, array $fieldnames = null) {
		/**
		 * array $rules deve ser definido por:
		 * ['NomeDoCampo' => 'OpçãoDeValidação1|OpçãoDeValidação2|...']
		 * OBS: o nome do campo deve corresponder ao nome do campo no array $data.
		 *
		 * Alterar os textos das mensagens de erro:
		 * Buscar as linhas iniciadas em  $arrayErrors[]  e alterar o texto depois de  $field . 
		 *
		 * OPÇÕES DE VALIDAÇÃO - SEPARADAS POR | NA CHAMADA:
		 * - required (campos obrigatórios)
		 * - int (números inteiros)
		 * - decimal (números decimais)
		 * - alpha (apenas letras e espaço)
		 * - alphanum (apenas letras, números e espaço)
		 * - email (email com sintaxe válida)
		 * - min:X (tamanho mínimo de caracteres definido por X)
		 * - max:X (tamanho máximo de caracteres definido por X)
		 * - equal:Y (deve ser igual ao campo Y)
		 *
		 * Retorna um array com todas as mensagens de erro encontradas, caso haja.
		 * Caso contrário, retorna FALSE, indicando que não encontrou erros.
		 */

		$errors = FALSE;
		$arrayErrors = array();

		foreach ($rules as $field => $fieldrules) {
			$rule = explode('|', $fieldrules);
			$value = trim($data[$field]);
			$errorsInput[$field] = '';

			foreach ($rule as $singlerule) {
				$singlerule = strtolower($singlerule);

				// Checa pela condição "Required"
				if ($singlerule == 'required' && empty($value)) {
					$errors = TRUE;
					$message = preg_replace(
					    '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationRequired
                    );
				 	$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um número inteiro
				if ($singlerule ==  'int' && !preg_match('/^[0-9]+$/', $value)) {
					$errors = TRUE;
                    $message = preg_replace(
                        '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationInt
                    );
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um número decimal
				if ($singlerule ==  'decimal' && !preg_match('/^[0-9\.\,]+$/', $value)) {
					$errors = TRUE;
                    $message = preg_replace(
                        '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationDecimal
                    );
					$arrayErrors[] = $message . '<br>';
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um valor de apenas letras
				if ($singlerule ==  'alpha' && !preg_match('/^[a-zA-ZÀ-úçÇ ]+$/', $value)) {
					$errors = TRUE;
					$$message = preg_replace(
                        '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationAlpha
                    );
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um valor alfanumérico
				if ($singlerule ==  'alphanum' && !preg_match('/^[a-zA-ZÀ-úçÇ0-9 ]+$/', $value)) {
					$errors = TRUE;
                    $message = preg_replace(
                        '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationAlphanum
                    );
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um email
				if ($singlerule ==  'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$errors = TRUE;
                    $message = preg_replace(
                        '/%field%/',
                        ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                        msgValidationEmail
                    );
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa por condições divididas por ":"
				if (sizeof($rulepart = explode(':', $singlerule)) > 1) {
					// Checa o tamanho mínimo da string
					if ($rulepart[0] == 'min' && strlen($value) < trim($rulepart[1])) {
						$errors = TRUE;
                        $message = preg_replace(
                            '/%field%/',
                            ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                            msgValidationMin
                        );
                        $message = preg_replace(
                            '/%quant%/',
                            $rulepart[1],
                            $message
                        );
			 			$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';

					}
					// Checa o tamanho máximo da string
					if ($rulepart[0] == 'max' && strlen($value) > trim($rulepart[1])) {
						$errors = TRUE;
                        $message = preg_replace(
                            '/%field%/',
                            ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                            msgValidationMax
                        );
                        $message = preg_replace(
                            '/%quant%/',
                            $rulepart[1],
                            $message
                        );
				 		$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';
			 		}
					// Checa se o campo deve ser igual a outro
					if ($rulepart[0] == 'equal' && $data[$field] != $data[$rulepart[1]]) {
						$errors = TRUE;
                        $message = preg_replace(
                            '/%field%/',
                            ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field)),
                            msgValidationEqual
                        );
                        $message = preg_replace(
                            '/%fieldEq%/',
                            ((($fieldnames[$rulepart[1]] != '') ? $fieldnames[$rulepart[1]]:$rulepart[1])),
                            $message
                        );
				 		$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';
			 		}
				}
			}
		}		
		

		// Se houver algum erro, retorna FALSE e redireciona para a página anterior. Senão, retorna TRUE
		if ($errors) {
			back()->flash('inputErrors', $errorsInput);
			return FALSE;
		} else
			return TRUE;
	}

    public static function make () {
        return new self;
    }
}