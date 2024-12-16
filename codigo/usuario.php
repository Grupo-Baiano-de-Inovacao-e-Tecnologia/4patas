<?php
class Usuario {
    private $conn;
    private $table = "usuario";

    public $id;
    public $nome;
    public $email;
    public $senha;
    public $data_nascimento;
    public $telefone;
    public $cpf;
    public $cep;
    public $rua;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $estado;
    public $data_cadastro;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (nome, email, senha, data_nascimento, telefone, cpf, cep, rua, numero, complemento, bairro, cidade, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssssssss",
            $this->nome,
            $this->email,
            $this->senha,
            $this->data_nascimento,
            $this->telefone,
            $this->cpf,
            $this->cep,
            $this->rua,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado);
        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $this->nome = $row['nome'];
            $this->email = $row['email'];
            $this->senha = $row['senha'];
            $this->data_nascimento = $row['data_nascimento'];
            $this->telefone = $row['telefone'];
            $this->cpf = $row['cpf'];
            $this->cep = $row['cep'];
            $this->rua = $row['rua'];
            $this->numero = $row['numero'];
            $this->complemento = $row['complemento'];
            $this->bairro = $row['bairro'];
            $this->cidade = $row['cidade'];
            $this->estado = $row['estado'];
            $this->data_cadastro = $row['data_cadastro'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET nome = ?, email = ?, data_nascimento = ?, telefone = ?, cpf = ?, cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, senha = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssssssssi", 
            $this->nome,
            $this->email,
            $this->data_nascimento,
            $this->telefone,
            $this->cpf,
            $this->cep,
            $this->rua,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->senha,
            $this->id);
        return $stmt->execute();
    }
    

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        return $stmt->execute();
    }
}
?>
