<?php
class SolicitacaoAdocao {
    private $conn;
    public $id;
    public $usuario_id;
    public $animal_id;
    public $nome_usuario;
    public $email_usuario;
    public $telefone_usuario;
    public $cpf_usuario;
    public $cep_usuario;
    public $idade_usuario;
    public $nome_animal;
    public $idade_animal;
    public $sexo_animal; 
    public $formulario;
    public $status;
    public $data_inicio;
    public $data_fim;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create() {
        $query = "INSERT INTO solicitacao_adocao 
                  (usuario_id, animal_id, nome_usuario, email_usuario, telefone_usuario, cpf_usuario, cep_usuario, idade_usuario, nome_animal, idade_animal, sexo_animal, formulario, status, data_inicio) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'analise', NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('iissssssssss', 
            $this->usuario_id, 
            $this->animal_id, 
            $this->nome_usuario, 
            $this->email_usuario, 
            $this->telefone_usuario, 
            $this->cpf_usuario, 
            $this->cep_usuario, 
            $this->idade_usuario, 
            $this->nome_animal, 
            $this->idade_animal, 
            $this->sexo_animal, 
            $this->formulario
        );
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM solicitacao_adocao WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($id, $novo_status) {
        $query = "UPDATE solicitacao_adocao 
                  SET status = ?, data_fim = NOW() 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $novo_status, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM solicitacao_adocao WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    public function solicitacaoExists() {
        $query = "SELECT id FROM solicitacao_adocao 
                  WHERE usuario_id = ? AND animal_id = ? 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $this->usuario_id, $this->animal_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function listSolicitacoesByAbrigo($abrigo_id) {
        $query = "SELECT sa.*, a.nome AS animal_nome, a.tipo AS animal_tipo, u.nome AS usuario_nome 
                  FROM solicitacao_adocao sa
                  JOIN animal a ON sa.animal_id = a.id
                  JOIN usuario u ON sa.usuario_id = u.id
                  WHERE a.abrigo_id = ? AND sa.status = 'analise'
                  ORDER BY sa.data_inicio ASC"; 
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $abrigo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function listAnimaisAprovadosByUser($usuario_id) {
        $query = "SELECT a.id, a.nome, a.foto 
                  FROM solicitacao_adocao sa
                  JOIN animal a ON sa.animal_id = a.id
                  WHERE sa.usuario_id = ? AND sa.status = 'aprovado'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function cancelarAdocao($animal_id, $usuario_nome) {
        $query = "UPDATE solicitacao_adocao 
                  SET status = 'recusado' 
                  WHERE animal_id = ? AND nome_usuario = ? AND status = 'aprovado'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $animal_id, $usuario_nome);

        return $stmt->execute();
    }
}
?>

