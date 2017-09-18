<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class View
{
    public function loadFile ($viewName, $data = null) {
        // Busca por arquivos .PHP, .HTML ou .PHTML para a view
        if (file_exists(BASEPATH . '/app/views/' . $viewName . '.php'))
            return require_once BASEPATH . '/app/views/' . $viewName . '.php';
        else if (file_exists(BASEPATH . '/app/views/' . $viewName . '.html'))
            return require_once BASEPATH . '/app/views/' . $viewName . '.html';
        else if (file_exists(BASEPATH . '/app/views/' . $viewName . '.phtml'))
            return require_once BASEPATH . '/app/views/' . $viewName . '.phtml';
        else
            return FALSE;
    }

    public function call ($viewName, $data = null) {
        /**
         * Sintaxe:
         *      !!section!! - Define no template onde será substituído pelo conteúdo definido no outro arquivo
         *      @section - Define o início do bloco que substituirá o código acima
         *      @endsection - Define o fim do bloco que substituirá a 'section'
         *      @extends('[file_path]') - Define qual o arquivo de template a ser complementado com o arquivo atual
         */



        // Obtém o código da view para ser analisado
        ob_start();
        $this->loadFile($viewName, $data);
        $viewCode = ob_get_contents();
        ob_end_clean();

        // Verifica se a view extende algum template (checa com aspas simples e com aspas duplas)
        preg_match_all("/\@extends\([\'|\"](.*?)[\'|\"]\)/", $viewCode, $extend);
        if(count($extend[1]) > 0)
            $extend = $extend[1][0]; // Posição em que é armazenado apenas o nome do arquivo extendido (apenas 1)

        // Verifica se há algum caminho antes da view, como em 'pasta/outrapasta/view'
        preg_match_all('/(.*?\/)/', $viewName, $extendPath);
        if(count($extendPath[1]) > 0)
            $extendPath = $extendPath[1][0];
            // Posição contendo o caminho até a a view chamada, para buscar o template a partir do mesmo
            // diretório em que a view está


        // Verifica se há 'sections' definidas na view
        preg_match_all("/\@section[\s]*([\s\S]*)@endsection/", $viewCode, $sections);
        if(count($sections) > 0)
            $sections = $sections[1]; // Posição em que são armazenados apenas os conteúdos das seções


        // Processa o conteúdo do template
        if(count($extend[1]) > 0) { // Inclui o template extendido caso exista
            ob_start();
            $this->loadFile($extendPath . $extend);
            $fileHtml = ob_get_contents();
            ob_end_clean();
        } else {
            // Define o código da própria view como código da página caso não extenda um template
            $fileHtml = $viewCode;
        }


        // Substitui cada 'section' do template pela section de índice correspondente na view
        foreach($sections as $section){
            $fileHtml = preg_replace('/!!section!!/', $section, $fileHtml, 1);
        }

        // Exibe o código da página
        echo $fileHtml;
    }



    public static function make () {
        return new self;
    }
}
