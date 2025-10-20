<?php

namespace App\Core\Interfaces;

interface IBaseRepository
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    // 👉 gelişmiş metotların sözleşmesini da buraya ekleyelim
    public function paginate($perPage = 10);
    public function filter(array $conditions);
    public function search($keyword, array $fields);
}
