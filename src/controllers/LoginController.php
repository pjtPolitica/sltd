<?php
namespace src\controllers;

use \core\Controller;
use \src\handler\LoginHandler;
use \src\models\Precadastro;
use \src\models\Serie;
use \src\models\Perfil;

class LoginController extends Controller {

    // confere
    public function entrar() {
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $this->render('login',['flash' => $flash]);
    }

    // confere
    public function entrarAction(){
        $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'senha');
        
        // para evitar invasão XSS
        $usuario=htmlspecialchars($usuario);
        
        if($usuario && $senha){
            if(LoginHandler::existeUsuario($usuario)){

                $token = LoginHandler::verificarLogin($usuario, $senha);

                if($token){
                    $_SESSION['token'] = $token;
                    $pessoa = LoginHandler::verificaToken($token);
                    $perfil = LoginHandler::verificaPerfil($pessoa['id_perfil']);
                    if($perfil['nome'] === 'aluno'){
                        $this->redirect('/');
                    }
                    if($perfil['nome'] === 'professor'){
                        $this->redirect('/professor');
                    }
                    if($perfil['nome'] === 'gestor'){
                        $this->redirect('/gestor');
                    }
                    if($perfil['nome'] === 'administrador'){
                        $this->redirect('/administrador');
                    }

                }else{
                    $_SESSION['flash'] = 'Os dados não conferem.';
                    $this->redirect('/login');
                }
            }else{
                $_SESSION['flash'] = 'Os dados não conferem.';
                $this->redirect('/login');
            }
        }else{
            $_SESSION['flash'] = 'Preencha todos os dados.';
            $this->redirect('/login');
        }

    }
    
    // confere
    public function resetarSenha(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $this->render('resetarsenha',['flash' => $flash]);
    }

    // confere
    public function resetarSenhaAction(){
        $dataNascimento = filter_input(INPUT_POST, 'dataNascimento', FILTER_SANITIZE_STRING);
        $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'novaSenha');
        
        // para evitar invasão XSS
        $usuario=htmlspecialchars($usuario);

        
        if($usuario && $senha){
            
            $dataNascimento = explode('/',$dataNascimento);
            if(count($dataNascimento) != 3){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/resetarsenha');
            }
            
            $dataNascimento = $dataNascimento[2].'-'.$dataNascimento[1].'-'.$dataNascimento[0];
            if(strtotime($dataNascimento) === false){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/resetarsenha');
            }
            
            if(LoginHandler::existeUsuario($usuario)){
                echo $dataNascimento."<br>";
                echo $usuario."<br>";
                echo $senha."<br>";

                if(LoginHandler::atualizaSenha($usuario, $senha, $dataNascimento)){
                    $this->redirect('/login');
                }
            }else{
                $_SESSION['flash'] = 'Os dados não conferem.';
                $this->redirect('/resetarsenha');
            }

        }else{
            $_SESSION['flash'] = 'Preencha todos os dados.';
            $this->redirect('/resetarsenha');
        }


    }

    // confere
    public function chave(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $chave = filter_input(INPUT_POST,'chave', FILTER_SANITIZE_STRING);
        $chave=htmlspecialchars($chave);

        $this->render('chave', ['flash' => $flash]);
    }
    
    // confere
    public function chaveAction(){
        $chave = filter_input(INPUT_POST,'chave', FILTER_SANITIZE_STRING);
        $chave=htmlspecialchars($chave);

        if($chave){
            $dados = Precadastro::select()->where('chave', $chave)->one();

            if($dados['status'] === 'ativo'){
                $_SESSION['chave'] = $dados['chave'];
                $_SESSION['flash'] = '';
                $this->redirect('/cadastrar');
            }
        }
        
        $_SESSION['flash'] = 'Chave de Acesso Inválida.';
        $this->redirect('/chave');

    }

    //  confere
    public function cadastrar(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $precadastro = [];
        $chave = $_SESSION['chave'];
        $dados = Precadastro::select()->where('chave', $chave)->one();
        if($dados){
            $newDados = new Precadastro();
            $newDados->id = $dados['id'];
            $newDados->nome = $dados['nome'];
            $data = $dados['data_nascimento'];
            $data = explode('-',$data);
            $data = $data['2'].'/'.$data['1'].'/'.$data['0'];
            $newDados->dataNascimento = $data;
            if($dados['id_serie'] != null){
                $serie = Serie::select()->where('id', $dados['id_serie'])->one();
                $newDados->serie = $serie['ano'].'º ano '.strtoupper($serie['turma']);
            }else{
                $newDados->serie = 'Não tem Série';
            }
            $perfil = Perfil::select()->where('id', $dados['id_perfil'])->one();
            $newDados->perfil = ucfirst($perfil['nome']);
            $titulo = ucfirst($perfil['nome']);

            $precadastro[] = $newDados;

            $this->render('cadastrar', ['flash' => $flash, 'precadastro' => $precadastro, 'titulo' => $titulo]);
        }

    }
    
    // confere
    public function cadastrarAction(){
        $chave = $_SESSION['chave'];
        $dados = Precadastro::select()->where('chave', $chave)->one();
        $nome = $dados['nome'];
        $perfil = $dados['id_perfil'];
        $turma = $dados['id_serie'];
        $dataNascimento = $dados['data_nascimento'];
        $usuario = filter_input(INPUT_POST, 'usuario', FILTER_SANITIZE_STRING);
        $senha = filter_input(INPUT_POST, 'senha');
        $termo = filter_input(INPUT_POST, 'termo', FILTER_SANITIZE_STRING);
        
        // para evitar invasão XSS
        $usuario=htmlspecialchars($usuario);
        $termo=htmlspecialchars($termo);
        
        if($usuario && $senha && $termo){
            if(LoginHandler::existeUsuario($usuario) === false){
                $token = LoginHandler::addPessoa($nome, $perfil, $turma, $dataNascimento, $usuario, $senha, $termo);
                echo $token;
                $_SESSION['token'] = $token;
                $_SESSION['chave'] = '';
                if($dados['status'] == 'ativo'){ // COLOCAR A PALAVRA EFETIVO PARA CHAVE CADASTRADA (ALTERAR NA BUSCA DO ADMINISTRADOR.)
                    Precadastro::update()
                        ->set('status', 'efetivo')
                        ->where('id', $dados['id'])
                    ->execute();
                }

                $this->redirect('/listaravatar');
            }else{
                $_SESSION['flash'] = 'Esse nome de usuário já existe.';
                $this->redirect('/cadastrar');
            }

        }else{
            $_SESSION['flash'] = 'Preencha todos os dados.';
            $this->redirect('/cadastrar');
        }
    }

    // confere
    public function listaravatar(){

        
        $avatar = LoginHandler::getAvatar();
        
        $this->render('listaravatar',['avatar' => $avatar]);
    }
    
    // confere
    public function escolheravatar($args){
        
        $args = intval($args['id']);
        if(is_int($args)){
            
            $pessoa = LoginHandler::updateAvatar($_SESSION['token'], $args);

        }
        
        $this->redirect('/login');
    }


}