<?php
require_once __DIR__ . '/../models/UserModel.php';

class UserController {
    private $model;

    public function __construct($company_id = null) {
        $this->model = new UserModel($company_id);
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
}
