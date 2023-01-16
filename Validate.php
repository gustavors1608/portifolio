<?php 
//arquivo utilizado para validar e sanitizar os dados enviados pelo user

namespace tools;

class Validate{

    /**
     * valida e sanitiza numeros inteiros
     * @param $dft_subst - 
     *      se for true e $int for invalido, retorna o $default
    */ 
    public function V_int($int, $min = 0, $max = 250, $dft_subst = false, $default = 16){

        $sanitized_int = filter_var($int, FILTER_VALIDATE_INT, array("options" => array("min_range"=>$min, "max_range"=>$max)));
        
        if ($sanitized_int === false) {
            if($dft_subst == false){
                return false;//valor invalido e nao deve substituir por valor padrao
            }else{
                return $default;
            }
        } else {
            return $sanitized_int;
        }
    }

    /**
    * permite somete dados como o alfabeto, numeros, _ , - e ponto.
    * erros caso: vazio,tamanhos incorretos, caracteres nao permitidos, 
    */
    public function V_string($str, $min = 0, $max = 50,$dft_subst = false, $default = ''){
        $size = strlen($str);
        if(($size > $min or $size < $max) and $size != 0){
           
            if (preg_match('/^[\w.-:]*$/',$str)) {
                $data = trim($str);
                $data = stripslashes($data);//creio q tao sendo usados a toa
                $data = htmlspecialchars($data);//bom usar na saida pra evitar xss
                return $data;
            }
        }
        //ou retorna a string ou retorna o valor padrao/false
        if($dft_subst == false){
            return false;
        }else{
            return $default;
        }
    }


    function validate_numbers($numbers){
        if(is_int($numbers)){
            return $numbers;
        }else{
            throw new \Exception('O Seguinte Numero eh invalido: '. $numbers);
            return false;
        }
    }
    function validate_boolean($boolean){
        return filter_var( $boolean, FILTER_VALIDATE_BOOLEAN);
    }
    function validate_string($data) {
        if($data == ''){
            throw new \Exception(' String vazia ');
            return false;
        }else{
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data,ENT_QUOTES);
            return $data;
        }
    }

}

  
?>