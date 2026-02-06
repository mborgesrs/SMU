<?php
require_once __DIR__ . '/../models/ClienteModel.php';

class ClienteController {
    private $model;

    public function __construct() {
        $this->model = new ClienteModel();
    }

    public function index($search = '') {
        return $this->model->getAll($search);
    }

    public function show($id) {
        return $this->model->getById($id);
    }

    public function store($data) {
        return $this->model->create($data);
    }

    public function update($id, $data) {
        return $this->model->update($id, $data);
    }

    public function destroy($id) {
        return $this->model->delete($id);
    }

    public function toggleStatus($id) {
        return $this->model->toggleStatus($id);
    }
}
