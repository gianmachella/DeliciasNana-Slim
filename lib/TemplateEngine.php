<?php

namespace Library;

class TemplateEngine
{
    private $archivo;
    private $variables = array();

    public function __construct($ruta){
        $this->archivo = file_get_contents($ruta);
    }

    function addVariable($variable, $contenido){
        $this->variables[$variable] = $contenido;
    }

    function verVariables(){
        return $this->variables;
    }

    function render(){
        $nuevo="";
        $entro=false;
        $llaveApertura=0;
        $llaveCierre=0;
        $cambio = $this->archivo;

        foreach ($this->variables as $key => $value){
            $cambio = str_replace("{{".$key."}}", $value, $cambio);
        }
        return $cambio;
    }
}