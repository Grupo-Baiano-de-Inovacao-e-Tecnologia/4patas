<?php
class Animal {
    private $conn;
    private $table = "animal";

    public $id;
    public $tipo;
    public $nome;
    public $data_nascimento;
    public $sexo;
    public $castrado;
    public $porte;
    public $descricao;
    public $foto;
    public $data_cadastro;
    public $abrigo_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " (tipo, nome, data_nascimento, sexo, castrado, porte, descricao, foto, abrigo_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssi", 
            $this->tipo, 
            $this->nome, 
            $this->data_nascimento, 
            $this->sexo, 
            $this->castrado, 
            $this->porte, 
            $this->descricao, 
            $this->foto, 
            $this->abrigo_id
        );
        if ($stmt->execute()) {
            return true;
        } else {
            echo "Erro ao executar a consulta: " . $stmt->error;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            $this->tipo = $row['tipo'];
            $this->nome = $row['nome'];
            $this->data_nascimento = $row['data_nascimento'];
            $this->sexo = $row['sexo'];
            $this->castrado = $row['castrado'];
            $this->porte = $row['porte'];
            $this->descricao = $row['descricao'];
            $this->foto = $row['foto'];
            $this->data_cadastro = $row['data_cadastro'];
            $this->abrigo_id = $row['abrigo_id'];
            return true;
        } else {
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET tipo = ?, nome = ?, data_nascimento = ?, sexo = ?, castrado = ?, porte = ?, descricao = ?, foto = ?, abrigo_id = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssssssssii", 
            $this->tipo, 
            $this->nome, 
            $this->data_nascimento, 
            $this->sexo, 
            $this->castrado, 
            $this->porte, 
            $this->descricao, 
            $this->foto, 
            $this->abrigo_id, 
            $this->id
        );
        if ($stmt->execute()) {
            return true;
        } else {
            echo "Erro ao executar a consulta: " . $stmt->error;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $this->id);
        if ($stmt->execute()) {
            return true;
        } else {
            echo "Erro ao executar a consulta: " . $stmt->error;
        }
        return false;
    }

    public function calcularIdade() {
        $data_nascimento = new DateTime($this->data_nascimento);
        $data_atual = new DateTime();
        $idade = $data_atual->diff($data_nascimento);
        $anos = $idade->y;
        $meses = $idade->m;
        if ($anos > 0) {
            return $anos === 1 ? '1 ano' : $anos . ' anos';
        } else {
            return $meses === 1 ? '1 mês' : $meses . ' meses';
        }
    }
}
?>
