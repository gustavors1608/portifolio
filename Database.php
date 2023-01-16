<?php

namespace geral;

//obtem os dados de configuracao
$ini = include(get_locate().'config/conf.php');

function get_locate(){
    $parts = explode('.',$_SERVER['SERVER_NAME']);

    $local = '';

    if($parts[0] == 'develop'){
        // develop
        $local = '/home/gustavousina/develop.gustavo.usina.dev/';
    }elseif($parts[0] == 'beta'){
        // beta
        $local = '/home/gustavousina/beta.gustavo.usina.dev/';
    }elseif($parts[0] == 'gustavo'){
        // producao
        $local = '/home/gustavousina/public_html/';
    }else{
        //temos um lugar nao conhecido
        echo "erro interno, localizacao nao encontrada";
    }
    return $local;
}

//usado constantes por que  ao usar 'global $ini' nao reconhece os dados
define('DB_NAME', $ini['db_name']);
define('DB_PASS', $ini['db_pass']);
define('DB_USER', $ini['db_user']);
define('DB_HOST', $ini['db_host']);


class Database{
    private $conn;    

    /**
     * Cria o objeto com a conexao ao db
     * @return string|\mysqli 
     *   retorna objeto $conn do banco de dados
     */
    public function __construct($db_name = DB_NAME ){
        try {
            $conn = new \mysqli(DB_HOST, DB_USER, DB_PASS, $db_name);

            if ($conn->connect_error) {//verifica se teve e porque ouve um erro de con.
                throw new \Exception("Connection failed: " . $conn->connect_error);
            }
            $this->conn = $conn;

        } catch (\Throwable $th) {
            //Logs::New(1,'Database::__contruct', $th->getMessage());//pode gerar um log txt para verificar se teve algum erro
            
            echo 'Opsss, algo deu errado. Tente novamente mais tarde .';
        }
    }

    //finaliza a conexao quando o codigo termina ou sai do escopo
    public function __destruct(){
        try {
            
            $this->conn->close();

        } catch (\Throwable $th) {
            //Logs::New(1,'Database::__destruct', $th->getMessage());
            
            echo 'Opsss, algo deu errado. Tente novamente mais tarde.';
        }
    }

    /**
     * realiza uma busca no banco de dados, pra mais detalhes de uso, consultar o metodo "insert"
     * exemplo de uso: $db->Select('relacional', 'nome_user', '`id` = ?', 'i', array(1))
     */
    public function Select($tabela, $colunas,$where, $types, $variables){
        
        try{

            $stmt = $this->conn->prepare("SELECT $colunas FROM $tabela WHERE $where");
            $stmt->bind_param($types, ...$variables); // bind parameters

            $status = $stmt->execute();

            if ($status == false) {
                //Logs::New(1,'Database::Update', '$stmt->execute nao foi executado com sucesso : '.$types.'  '.$variables);
            }

            $result = $stmt->get_result();

            $increment = 0;
            $arr_return = array();


            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $arr_return[$increment] = $row;
                    $increment++;//a cada linha será um elemento do array
                }
                $arr_return['sucess'] = true;
                $arr_return['lines'] = $increment;
                $arr_return['fallback'] = false;
                return $arr_return;

            }

            $arr_return['sucess'] = false;
            $arr_return['lines'] = 0;
            $arr_return['error'] = 'Nenhum resultado encontrado tabela:'.$tabela.' where:'.$where.' variaveis: '. implode(', ',$variables);

            //TODO: ver uma forma que dê para gerar um array com os dados (dados fake ou em cache)que foram pedidos e retorna os mesmos(caso precise de dados reais so avaliar se sucess == true)            
            $arr_return['fallback'] = true;
            return $arr_return;
        
            
        }catch(\Throwable $th){
            //Logs::New(1,'Database::Update', $th->getMessage());
            Logs::Infos_user('Opsss, algo deu errado, Tente novamente mais tarde. ' . $th->getMessage(). ' '. $tabela. ' '. $colunas);
        }
        
    }


    /**
     * Função que executa um 'UPDATE' no banco de dados, pra mais detalhes veja o metodo insert
     * example: $db->Update('relacional', '`nome_user`=?', 'id = ?', 'si', array('gustavo user',1));
    */
    public function Update($tabela, $colunas,$where, $types, $variables){
        try{
            $stmt = $this->conn->prepare("UPDATE $tabela SET $colunas WHERE $where"); // prepare statement
            $stmt->bind_param($types, ...$variables); // bind parameters

            $status = $stmt->execute();
            /* verifique sempre se a execução() foi bem sucedida */
            if ($status == false) {
                Logs::New(1,'Database::Update', '$stmt->execute nao foi executado com sucesso : '.$types.'  '.$variables);
                return false;
            }

            if ($stmt->affected_rows > 0) {
                return true;
            }else{
                return false;
            }

        }catch(\Throwable $th){
            Logs::New(1,'Database::Update', $th->getMessage());
            
            return false;
        }
        
    }

    /**
     * $tabela = nome da tabela 
     * $colunas = nomes das colunas separados por ,  
     * $types = tipos de dados das var: 
     *      i - integer
     *      d - double
     *      s - string
     *      b - BLOB
     *  $arr_variables = variaveis separadas por , 
     *  @example: $db->Insert('users', 'username, password, name, email', 'ssss', $arr_variables)
     *  credito: https://stackoverflow.com/questions/48287330/database-class-insert-method
     */
    public function Insert(string $tabela, string $colunas, string $types, array $variables) {
        // Generate values string based on the value of $types
        $replace = array("i", "d", "s", "m"); // characters to replace
        $replace_with = array("?,", "?,", "?,", "?,"); // characters to replace with
        $values = str_replace($replace, $replace_with, $types); // replace 'i', 'd', 's', 'm' with '?,'
        $values = rtrim($values,", "); // remove last ',';

        $stmt = $this->conn->prepare("INSERT INTO $tabela ($colunas) VALUES ($values)"); // prepare statement
        $stmt->bind_param($types, ...$variables); // bind parameters
        $stmt->execute(); // insert to database 
        return $stmt->affected_rows;
    }


    /** 
     * funcao criada para casos onde deve garantir que salve um valor em uma coluna de uma tabela, para 1 user
     * tenta realizar um update, caso nao consiga, verifica se encontra alguma referencia(para nao add itens repetidos)
     *  caso mesmo assim nao encontre uma referencia ao conteudo, entao insere pq realmente nao existe
     * @return 0 -> nao fez update pq o dado que foi passado ja é igual ao dado salvo
     * @return 1 -> fez um insert
     * @return 2 -> fez um update
     * 
     * @example $db->Update_or_insert('logs','log','hello world','codigo_disp','xxxxxx');
     */
    public function Update_or_insert(string $tabela, string $valor_atualizar,string $conteudo, string $where_name ,string $where_value){
        $is_add = false;
        $is_add = $this->Update($tabela, " `$valor_atualizar`=? ", "`$where_name` = ?", 'ss', array($conteudo, $where_value));
    
        if($is_add == false){
            $retorno_select = $this->Select($tabela, 'id', "`$where_name` = ?", 's', array($where_value));
            if($retorno_select['lines'] == 0){//select para ver se nao existe ja(para nao add 50 vezes o mesmo caso de problema)
                $this->Insert($tabela, $valor_atualizar.",$where_name", 'ss', array($conteudo,$where_value));
                //echo 'inseriu';
                return 1;
            }else{
                //echo 'nao atualizou pois esse dado ja estava no db';
                return 0;
            }
        }else{
            //echo 'atualizou';
            return 2;
        }
    }

    }

?>