<?php
namespace src\controllers;

use \core\Controller;
use \src\handler\LoginHandler;
use \src\models\Pessoa;
use \src\models\Perfil;
use \src\models\Avatar;
use \src\models\Precadastro;

class AdministradorHomeController extends Controller {

    private $usuarioLogado;

    public function __construct(){
        $this->usuarioLogado = LoginHandler::checkLogin();
        if(LoginHandler::checkLogin() === false){
            $this->redirect('/login');
        }
        $pessoa = Pessoa::select()->where('id', $this->usuarioLogado->id)->one();
        $perfil = Perfil::select()->where('id', $pessoa['id_perfil'])->one();

        if($perfil['nome'] !== 'administrador'){
            $_SESSION['token'] = '';
            $this->redirect('/login');
        }
    }

    public function index() {
        $perfil = Perfil::select()->where('nome', 'gestor')->one();
        $dados = Pessoa::select()->where('id_perfil', $perfil['id'])->get();
        
        $gestor = [];
        foreach($dados as $dadosItem){
            $newPessoa = new Pessoa();
            $newPessoa->id = $dadosItem['id'];
            $newPessoa->nome = $dadosItem['nome'];
            $avatar = Avatar::select()->where('id', $dadosItem['id_avatar'])->one();
            $newPessoa->avatar = $avatar['arquivo'];
            
            $gestor[] = $newPessoa;
        }


        $this->render('administrador/home', ['gestor' => $gestor]);
    }
    
    // confere
    public function cadastrar(){
        $flash = '';
        if(!empty($_SESSION['flash'])){
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        $this->render('administrador/cadastrar',['flash' => $flash]);
    }

    // confere
    public function cadastrarAction(){
        $nome = filter_input(INPUT_POST,'nome', FILTER_SANITIZE_STRING);
        $dataNascimento = filter_input(INPUT_POST, 'dataNascimento', FILTER_SANITIZE_STRING);

        $nome=htmlspecialchars($nome);

        if($nome){
            $dataNascimento = explode('/',$dataNascimento);
            if(count($dataNascimento) != 3){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/administrador/cadastrar');
            }
            
            $dataNascimento = $dataNascimento[2].'-'.$dataNascimento[1].'-'.$dataNascimento[0];
            if(strtotime($dataNascimento) === false){
                $_SESSION['flash'] = 'Data de nascimento inválida.';
                $this->redirect('/administrador/cadastrar');
            }

            $perfil = Perfil::select()->where('nome', 'gestor')->one();
            
            $dadosCount = precadastro::select()->get();
            $letras = array('b','c','d','f','g','h','j','l','m','n','p','q','r','s','t','v','x','z');
            $auxLetra = array_rand($letras,4);
            $chave = $letras[$auxLetra[0]].$letras[$auxLetra[1]].$letras[$auxLetra[2]].$letras[$auxLetra[3]].count($dadosCount);
            //$chave = md5($dataNascimento.time());

            Precadastro::insert([
                'nome' => $nome,
                'data_nascimento' => $dataNascimento,
                'chave' => $chave,
                'id_perfil' => $perfil['id']
            ])->execute();

            $this->redirect('/administrador/precadastro');
        }else{
            $_SESSION['flash'] = 'Preencha todos os dados.';
            $this->redirect('/administrador/cadastrar');
        }
    }

    // confere
    public function precadastro(){
        $perfil = Perfil::select()->where('nome', 'gestor')->one();
        $dados = Precadastro::select()->where('id_perfil', $perfil['id'])->get();

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

        $this->render('administrador/precadastro', ['precadastro' => $precadastro]);
    }

    // confere
    public function precadastroAction(){
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

        $this->render('administrador/precadastro', ['precadastro' => $resultado]);

    }

    // confere
    public function inativarGestor($args){
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
        
        $this->redirect('/administrador/precadastro');

    }

    // confere
    public function sair(){
        $_SESSION['token'] = '';
        $this->redirect('/login');
    }

}