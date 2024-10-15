<?php
namespace src\controllers;

use \core\Controller;
use \src\handler\LoginHandler;
use \src\handler\ProfessorHandler;
use \src\models\Pessoa;
use \src\models\Perfil;

class ProfessorHomeController extends Controller {

    private $usuarioLogado;

    public function __construct(){
        $this->usuarioLogado = LoginHandler::checkLogin();
        if(LoginHandler::checkLogin() === false){
            $this->redirect('/login');
        }
        $pessoa = Pessoa::select()->where('id', $this->usuarioLogado->id)->one();
        $perfil = Perfil::select()->where('id', $pessoa['id_perfil'])->one();

        if($perfil['nome'] !== 'professor'){
            $_SESSION['token'] = '';
            $this->redirect('/login');
        }
    }

    public function index() {

        $pessoa = ProfessorHandler::getAlunoTurma($this->usuarioLogado->turma);

        $this->render('professor/home', ['pessoa' => $pessoa]);
    }

    public function sobre() {
        $this->render('adm/sobre');
    }

    public function sair(){
        $_SESSION['token'] = '';
        $this->redirect('/login');
    }

}