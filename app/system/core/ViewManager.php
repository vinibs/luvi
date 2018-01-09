<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class ViewManager
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

        $pageSections = array();
        do {
            // Define o nome da última view carregada como sendo a atual, antes de processar
            // os dados (para comparar posteriormente)
            $lastView = $viewName;

            // Obtém o código HTML da view para ser analisado
            ob_start();
            $this->loadFile($viewName, $data);
            $viewCode = ob_get_contents();
            ob_end_clean();

            // Verifica se a view extende algum template (checa com aspas simples e com aspas duplas)
            preg_match_all("/\@extends\([\'|\"](.*?)[\'|\"]\)/", $viewCode, $extend);
            if (count($extend[1]) > 0) {
                $extend = $extend[1][0];
            } // Posição em que é armazenado apenas o nome do arquivo extendido (apenas 1)

            // Verifica se há algum caminho antes da view, como em 'pasta/outrapasta/view'
            preg_match_all('/(.*?\/)*/', $viewName, $extendPath);
            if (count($extendPath[0]) > 0) {
                $extendPath = $extendPath[0][0];
            }
            // Posição contendo o caminho até a a view chamada, para buscar o template a partir do mesmo
            // diretório em que a view está


            // Verifica se há uma ou mais 'section' nomeadas na view
            preg_match_all("/\@section\([\'|\"](.*?)[\'|\"]\)[\s]*([\s\S]*?)@endsection/", $viewCode, $sections);
            // Caso haja ao menos uma section nomeada, organiza seus dados no vetor
            if(count($sections[1]) > 0){
                foreach($sections[1] as $j => $section){
                    $pageSections[] = array('name' => $sections[1][$j], 'content' => $sections[2][$j]);
                }
            }

            if(count($extend[1]) > 0) { // Define o nome do template a ser carregado a seguir
                $viewName = $extendPath . $extend;
            }
            // Obtém o HTML do template principal até aqui

            // Processa as seções das páginas, após o carregamento de todas as views
            if(count($pageSections) > 0 && $lastView == $viewName) {
                // Para cada seção encontrada, substitui o comando !!section('...')!! da view principal
                // pelo equivalente da view que extende


                // Regex para encontrar trechos como "!!section('...')!!", usada em ambos os foreach abaixo,
                // na hora de fazer as substituições das seções
                $sectionPattern = '/!!section\([\'|\"]' . $section['name'] . '[\'|\"]\)!!/';

                // Analisa todas as seções obtidas e as substitui pelo conteúdo definido previamente
                foreach ($pageSections as $section) {
                    $viewCode = preg_replace(
                        $sectionPattern,
                        $section['content'],
                        $viewCode,
                        1
                    );

                    // [Verifica para cada seção se há uma seção interna]
                    // Verifica se, após substituir, ainda existe uma nova seção, dentro do conteúdo vindo de outra
                    // seção (Como "@section(x) ... !!section(y)!! ... @endsection")
                    foreach ($pageSections as $section) {
                        $viewCode = preg_replace(
                            $sectionPattern,
                            $section['content'],
                            $viewCode,
                            1
                        );
                    }

                }
            }

        } while($lastView != $viewName);
        // Repete enquanto a última view carregada não for igual à atual (será a última, a raiz)


        echo $viewCode;
        // Exibe o HTML carregado
    }



    public static function make () {
        return new self;
    }
}
