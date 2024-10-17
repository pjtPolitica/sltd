<?php
namespace src\controllers;

use \core\Controller;
use \src\handler\LoginHandler;
use \src\handler\GestorHandler;
use \src\models\Pessoa;
use \src\models\Perfil;
use \src\models\Avatar;
use \src\models\Precadastro;

class GestorHomeController extends Controller {

    private $usuarioLogado;

    public function __construct(){
        $this->usuarioLogado = LoginHandler::checkLogin();
        if(LoginHandler::checkLogin() === false){
            $this->redirect('/login');
        }
        $pessoa = Pessoa::select()->where('id', $this->usuarioLogado->id)->one();
        $perfil = Perfil::select()->where('id', $pessoa['id_perfil'])->one();

        if($perfil['nome'] !== 'administrador' && $perfil['nome'] !== 'gestor'){
            $_SESSION['token'] = '';
            $this->redirect('/login');
        }
    }

    public function index() {

        $perfilProfessor = Perfil::select()->where('nome', 'professor')->one();
        $perfilAluno = Perfil::select()->where('nome', 'aluno')->one();

        $dados = Pessoa::select()->where('id_perfil', 'in', [$perfilAluno['id'],$perfilProfessor['id']])->get();

        $pessoa = [];
        foreach($dados as $dadosItem){
            $newPessoa = new Pessoa();
            $newPessoa->id = $dadosItem['id'];
            $newPessoa->nome = $dadosItem['nome'];
            $serie = GestorHandler::getSerieId($dadosItem['id_serie']);
            $newPessoa->serie = $serie['ano'].'º ano '.strtoupper($serie['turma']);
            $avatar = Avatar::select()->where('id', $dadosItem['id_avatar'])->one();
            $newPessoa->avatar = $avatar['arquivo'];

            $pessoa[] = $newPessoa;
        }

        $this->render('gestor/home', ['pessoa' => $pessoa]);
    }

    // confere
    public function precadastro(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $perfil = GestorHandler::getPerfil();
        $serie = LoginHandler::serie();

        $ano = array_column($serie, 'ano');
        $ano = array_unique($ano);

        $this->render('gestor/precadastro',['flash' => $flash, 'perfil' => $perfil, 'ano' => $ano, 'serie' => $serie]);
    }

    // confere
    public function precadastroAction(){
        $nome = filter_input(INPUT_POST,'nome', FILTER_SANITIZE_STRING);
        $perfil = filter_input(INPUT_POST,'perfil', FILTER_VALIDATE_INT);
        $serie = filter_input(INPUT_POST,'turma', FILTER_VALIDATE_INT);
        $dataNascimento = filter_input(INPUT_POST, 'dataNascimento', FILTER_SANITIZE_STRING);

        $nome=htmlspecialchars($nome);

        if($nome && $perfil && $serie){
            $dataNascimento = explode('/',$dataNascimento);
            if(count($dataNascimento) != 3){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/gestor/precadastro');
            }
            
            $dataNascimento = $dataNascimento[2].'-'.$dataNascimento[1].'-'.$dataNascimento[0];
            if(strtotime($dataNascimento) === false){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/gestor/precadastro');
            }

            $dadosCount = precadastro::select('id')->get();
            $letras = array('b','c','d','f','g','h','j','l','m','n','p','q','r','s','t','v','x','z');
            $auxLetra = array_rand($letras,4);
            $chave = $letras[$auxLetra[0]].$letras[$auxLetra[1]].$letras[$auxLetra[2]].$letras[$auxLetra[3]].count($dadosCount);
            //$chave = md5($dataNascimento.time());

            Precadastro::insert([
                'nome' => $nome,
                'data_nascimento' => $dataNascimento,
                'id_serie' => $serie,
                'chave' => $chave,
                'id_perfil' => $perfil
            ])->execute();

            $this->redirect('/gestor');
        }else{
            $_SESSION['flash'] = 'Preencha todos os dados.';
            $this->redirect('/gestor/precadastro');
        }

    }

    // confere
    public function listarprecadastro(){
        $perfilAluno = Perfil::select()->where('nome', 'aluno')->one();
        $perfilProfessor = Perfil::select()->where('nome', 'professor')->one();
        $dados = Precadastro::select()->where('id_perfil', 'in', [$perfilAluno['id'], $perfilProfessor['id']])->get();

        $precadastro = [];
        foreach($dados as $dadosItem){
            $newPrecadastro = new Precadastro();
            $newPrecadastro->id = $dadosItem['id'];
            $newPrecadastro->nome = $dadosItem['nome'];
            if($dadosItem['status'] == 'ativo'){
                $newPrecadastro->status = 'Inativar';
            }elseif($dadosItem['status'] == 'inativo'){
                $newPrecadastro->status = 'Ativar';
            }else{
                $newPrecadastro->status = 'efetivo';
            }
            $newPrecadastro->chave = $dadosItem['chave'];
            
            $precadastro[] = $newPrecadastro;
        }

        $this->render('gestor/listarprecadastro', ['precadastro' => $precadastro]);
    }

    // confere
    public function listarprecadastroAction(){
        $pesquisa = filter_input(INPUT_POST,'pesquisa', FILTER_SANITIZE_STRING);

        $dados = Precadastro::select()->Where('nome', 'like','%'.$pesquisa.'%')->get();

        $resultado = [];
        if($dados){
            foreach($dados as $dadosItem){
                $newPrecadastro = new Precadastro();
                $newPrecadastro->id = $dadosItem['id'];
                $newPrecadastro->nome = $dadosItem['nome'];
                if($dadosItem['status'] == 'ativo'){
                    $newPrecadastro->status = 'Inativar';
                }elseif($dadosItem['status'] == 'inativo'){
                    $newPrecadastro->status = 'Ativar';
                }else{
                    $newPrecadastro->status = 'efetivo';
                }
                $newPrecadastro->chave = $dadosItem['chave'];

                $resultado[] = $newPrecadastro;
            }
        }

        $this->render('gestor/listarprecadastro', ['precadastro' => $resultado]);
    }

    // confere
    public function inativarprecadastro($args){
        $args = intval($args['id']);
        if(is_int($args)){
            $dados = Precadastro::select()->where('id', $args)->one();
            if($dados['status'] == 'ativo'){
                Precadastro::update()
                    ->set('status', 'inativo')
                    ->where('id', $dados['id'])
                ->execute();
            }elseif($dados['status'] == 'inativo'){
                Precadastro::update()
                    ->set('status', 'ativo')
                    ->where('id', $dados['id'])
                ->execute();
            }
        }
        
        $this->redirect('/gestor/listarprecadastro');
    }

    // confere
    public function cadastraravatar(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $this->render('gestor/cadastraravatar', ['flash' => $flash]);
    }

    // confere
    public function cadastraravatarAction(){
        $arquivo = $_FILES['arquivo'];
        $nome = filter_input(INPUT_POST,'nome', FILTER_SANITIZE_STRING);

        if($nome){
            if(isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['tmp_name'])){
                if($arquivo['type'] == 'image/png'){
                    
                    $nomeImagem = md5(time().rand(0,999)).'.png';
                    $diretorio = 'img/avatar/';
                    move_uploaded_file($arquivo['tmp_name'], $diretorio.$nomeImagem);
                    
                    GestorHandler::addAvatar($nome, $nomeImagem);
                    
                    $_SESSION['flash'] = '';
                    $this->render('gestor/cadastraravatar');
                }
            }else{
                $_SESSION['flash'] = 'Preencha todos os dados.';
                $this->redirect('/gestor/cadastraravatar');
            }
        }else{
            $_SESSION['flash'] = 'Preencha todos do dados.';
            $this->redirect('/gestor/cadastraravatar');
        }

    }

    // confere
    public function listaravatar(){

        $avatar = GestorHandler::getAvatar();

        $this->render('gestor/listaravatar',['avatar' => $avatar]);
    }

    // verificar se esta funcional
    public function alteraravatar($args){
        $args = intval($args['id']);
        
        if(is_int($args)){
            $avatar = GestorHandler::UpdateAvatar($args);

            // var_dump($avatar);

            if($avatar){
                $this->render('gestor/alteraravatar',['avatar' => $avatar]);
            }
        }
        // $this->redirect('/gestor/listaravatar');
    }

    // confere
    public function cadastraremoji(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $this->render('gestor/cadastraremoji', ['flash' => $flash]);
    }

    // confere
    public function cadastraremojiAction(){
        $arquivo = $_FILES['arquivo'];
        $nome = filter_input(INPUT_POST,'nome', FILTER_SANITIZE_STRING);
        $tipo = filter_input(INPUT_POST,'tipo', FILTER_SANITIZE_STRING);

        if($nome && $tipo){
            if(isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['tmp_name'])){
                if($arquivo['type'] == 'image/gif'){
                    
                    $nomeImagem = md5(time().rand(0,999)).'.gif';
                    $diretorio = 'img/emoji/';
                    move_uploaded_file($arquivo['tmp_name'], $diretorio.$nomeImagem);
                    
                    GestorHandler::addEmoji($nome, $tipo, $nomeImagem);
                    
                    $_SESSION['flash'] = '';
                    $this->render('gestor/cadastraremoji');
                }
            }else{
                $_SESSION['flash'] = 'Preencha todos os dados.';
                $this->redirect('/gestor/cadastraremoji');
            }
        }else{
            $_SESSION['flash'] = 'Preencha todos do dados.';
            $this->redirect('/gestor/cadastraremoji');
        }

    }

    // confere
    public function listaremoji(){

        $emoji = GestorHandler::getEmoji();

        $this->render('gestor/listaremoji',['emoji' => $emoji]);
    }

    // verificar se esta funcional
    public function alteraremoji($args){
        $args = intval($args['id']);
        
        if(is_int($args)){
            $emoji = GestorHandler::UpdateEmoji($args);

            // var_dump($emoji);

            if($emoji){
                $this->render('gestor/alteraremoji',['emoji' => $emoji]);
            }
        }
        // $this->redirect('/gestor/listaremoji');
    }

    // confere
    public function cadastrarpalavra(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $serie = LoginHandler::serie();

        $ano = array_column($serie, 'ano');
        $ano = array_unique($ano);

        $this->render('gestor/cadastrarpalavra',['flash' => $flash, 'ano' => $ano]);
    }

    // confere
    public function cadastrarpalavraAction(){
        $palavra = filter_input(INPUT_POST,'palavra', FILTER_SANITIZE_STRING);
        $arquivo = $_FILES['arquivo'];
        $ano = filter_input(INPUT_POST,'ano', FILTER_VALIDATE_INT);
        $nivel = filter_input(INPUT_POST,'nivel', FILTER_VALIDATE_INT);

        if($palavra && $ano && $nivel){
            if(isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['tmp_name'])){
                if($arquivo['type'] == 'audio/mpeg'){

                    $newNome = md5(time().rand(0,999)).'.mp3';
                    $diretorio = 'media/';
                    move_uploaded_file($arquivo['tmp_name'], $diretorio.$newNome);

                    GestorHandler::addPalavra($palavra, $newNome, $ano, $nivel);
                    
                    $_SESSION['flash'] = '';
                    $this->redirect('/gestor/cadastrarpalavra');
                }
            }else{
                $_SESSION['flash'] = 'Preencha todos do dados.';
                $this->redirect('/gestor/cadastrarpalavra');
            }
        }else{
            $_SESSION['flash'] = 'Preencha todos do dados.';
            $this->redirect('/gestor/cadastrarpalavra');
        }

    }

    // confere
    public function listarpalavra(){

        $palavra = GestorHandler::getPalavra();

        $this->render('gestor/listarpalavra',['palavra' => $palavra]);
    }

    public function listarpalavraAno($args){
        $args = intval($args['ano']);
        
        if(is_int($args)){

            $palavra = GestorHandler::getPalavraAno($args);
            
            $this->render('gestor/listarpalavra',['palavra' => $palavra]);
        }

    }

    // verificar se esta funcional
    public function alterarpalavra($args){
        $args = intval($args['id']);
        
        if(is_int($args)){
            $palavra = GestorHandler::UpdatePalavra($args);

            // var_dump($palavra);

            if($palavra){
                $this->render('gestor/alterarpalavra',['palavra' => $palavra]);
            }
        }
        // $this->redirect('/gestor/listarpalavra');
    }







    public function sobre() {
        $this->render('adm/sobre');
    }

    public function sair(){
        $_SESSION['token'] = '';
        $this->redirect('/login');
    }

}