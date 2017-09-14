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
					$message = '"'  . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve ser preenchido'; // PT-BR
				 	$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';				 	
				}

				// Checa pela condição de ser um número inteiro
				if ($singlerule ==  'int' && !preg_match('/^[0-9]+$/', $value)) {
					$errors = TRUE;
					$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve conter um número inteiro'; // PT-BR
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um número decimal
				if ($singlerule ==  'decimal' && !preg_match('/^[0-9\.\,]+$/', $value)) {
					$errors = TRUE;
					$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve conter um número decimal'; // PT-BR
					$arrayErrors[] = $message . '<br>';
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um valor de apenas letras
				if ($singlerule ==  'alpha' && !preg_match('/^[a-zA-ZÀ-úçÇ ]+$/', $value)) {
					$errors = TRUE;
					$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve conter apenas letras'; // PT-BR
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um valor alfanumérico
				if ($singlerule ==  'alphanum' && !preg_match('/^[a-zA-ZÀ-úçÇ0-9 ]+$/', $value)) {
					$errors = TRUE;
					$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve conter apenas letras e números'; // PT-BR
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa pela condição de ser um email
				if ($singlerule ==  'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$errors = TRUE;
					$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                        . '" deve conter um endereço de e-mail válido'; // PT-BR
					$arrayErrors[] = $message;
					$errorsInput[$field] .= $message . '<br>';
				}

				// Checa por condições divididas por ":"
				if (sizeof($rulepart = explode(':', $singlerule)) > 1) {
					// Checa o tamanho mínimo da string
					if ($rulepart[0] == 'min' && strlen($value) < trim($rulepart[1])) {
						$errors = TRUE;
				 		$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                            . '" deve conter ao menos ' . $rulepart[1]
				 			. ' characteres'; // PT-BR
			 			$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';

					}
					// Checa o tamanho máximo da string
					if ($rulepart[0] == 'max' && strlen($value) > trim($rulepart[1])) {
						$errors = TRUE;
				 		$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                            . '" deve conter no máximo ' . $rulepart[1]
				 			. ' characteres'; // PT-BR
				 		$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';
			 		}
					// Checa se o campo deve ser igual a outro
					if ($rulepart[0] == 'equal' && $data[$field] != $data[$rulepart[1]]) {
						$errors = TRUE;
				 		$message = '"' . ((($fieldnames[$field] != '') ? $fieldnames[$field]:$field))
                            . '" deve ser igual ao campo "'
				 			. $rulepart[1] . '"'; // PT-BR
				 		$arrayErrors[] = $message;
						$errorsInput[$field] .= $message . '<br>';
			 		}
				}
			}
			/*
			 * Lista de mensagens de erros em inglês (EN), respectivamente:
			 * $message = '"' . $field . '" is a required field'; // EN
			 * $message = '"' . $field . '" must be an integer'; // EN
			 * $message = '"' . $field . '" must be a decimal number'; // EN
			 * $message = '"' . $field . '" must have only letters'; // EN
			 * $message = '"' . $field . '" must have only letters and numbers'; // EN
			 * $message = '"' . $field . '" must be a valid email address'; // EN
			 * $message = '"' . $field . '" must have at least ' . $rulepart[1]	. ' characters'; // EN
			 * $message = '"' . $field . '" must have a maximum of ' . $rulepart[1] . ' characters'; // EN
			 * $message = '"' . $field . '" must be identical to "' . $rulepart[1] . '"'; // EN
			 */
		}		
		

		// Se houver algum erro, retorna FALSE e redireciona para a página anterior. Senão, retorna TRUE
		if ($errors) {
			back()->flash('inputErrors', $errorsInput);
			return FALSE;
		} else
			return TRUE;
	}
}