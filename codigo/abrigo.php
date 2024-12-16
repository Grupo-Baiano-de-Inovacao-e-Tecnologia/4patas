<?php
class Abrigo {
    private $conn;
    private $table = "abrigo";

    public $id;
    public $nome;
    public $cnpj_cpf;
    public $email;
    public $telefone;
    public $cep;
    public $rua;
    public $numero;
    public $complemento;
    public $bairro;
    public $cidade;
    public $estado;
    public $senha;
    public $site;
    public $criado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nome, cnpj_cpf, email, telefone, cep, rua, numero, complemento, bairro, cidade, estado, senha, site, criado_em) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssssssss", 
            $this->nome, 
            $this->cnpj_cpf, 
            $this->email, 
            $this->telefone, 
            $this->cep, 
            $this->rua, 
            $this->numero, 
            $this->complemento, 
            $this->bairro, 
            $this->cidade, 
            $this->estado, 
            $this->senha, 
            $this->site
        );
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT id, nome, cnpj_cpf, email, telefone, cep, rua, numero, complemento, bairro, cidade, estado, senha, site, criado_em 
                  FROM " . $this->table . " 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result(
                $this->id,
                $this->nome,
                $this->cnpj_cpf,
                $this->email,
                $this->telefone,
                $this->cep,
                $this->rua,
                $this->numero,
                $this->complemento,
                $this->bairro,
                $this->cidade,
                $this->estado,
                $this->senha,
                $this->site,
                $this->criado_em
            );
            $stmt->fetch();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nome = ?, cnpj_cpf = ?, email = ?, telefone = ?, cep = ?, rua = ?, numero = ?, complemento = ?, bairro = ?, cidade = ?, estado = ?, senha = ?, site = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssssssssssi", 
            $this->nome,
            $this->cnpj_cpf,
            $this->email,
            $this->telefone,
            $this->cep,
            $this->rua,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->estado,
            $this->senha,
            $this->site,
            $this->id
        );
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function listAbrigoseCanis() {
        $sql = "SELECT id, nome, telefone, rua, numero, bairro, cidade, estado, site FROM " . $this->table;
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $abrigos = [];
            while ($row = $result->fetch_assoc()) {
                $abrigos[] = $row;
            }
            return $abrigos;
        }
        return false; 
    }
}
?>
