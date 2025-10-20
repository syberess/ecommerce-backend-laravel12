<?php

namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // 🔹 Tüm kayıtları getir
    public function all()
    {
        return $this->model->all();
    }

    // 🔹 ID’ye göre bul
    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    // 🔹 Yeni kayıt oluştur
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // 🔹 Kayıt güncelle
    public function update($id, array $data)
    {
        $record = $this->model->findOrFail($id);
        $record->update($data);
        return $record;
    }

    // 🔹 Kayıt sil
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    // 🔹 Sayfalama desteği
    public function paginate($perPage = 10)
    {
        return $this->model->paginate($perPage);
    }

    // 🔹 Dinamik filtreleme (örnek: ['category_id' => 1])
    public function filter(array $conditions)
    {
        $query = $this->model->query();
        foreach ($conditions as $field => $value) {
            $query->where($field, $value);
        }
        return $query->get();
    }

    // 🔹 Arama desteği (örnek: search('kahve', ['name', 'description']))
    public function search($keyword, array $fields)
    {
        return $this->model->where(function ($query) use ($keyword, $fields) {
            foreach ($fields as $field) {
                $query->orWhere($field, 'LIKE', "%{$keyword}%");
            }
        })->get();
    }

    // 🔹 Dinamik filtre + sıralama + sayfalama kombinasyonu
    public function paginateWithFilters(array $filters = [], $perPage = 10, ?string $sortBy = null, string $sortOrder = 'asc')
    {
        $query = $this->model->query();

        // 🔸 Dinamik filtreleme
        foreach ($filters as $field => $value) {
            if (is_array($value) && count($value) === 2 && isset($value[0], $value[1])) {
                // Örnek: ['price' => [50, 100]]
                $query->whereBetween($field, $value);
            } elseif ($value !== null && $value !== '') {
                $query->where($field, $value);
            }
        }

        // 🔸 Dinamik sıralama
        if ($sortBy && in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            // Güvenlik: sadece modeldeki gerçek sütunlara izin verelim
            if (in_array($sortBy, $this->model->getFillable()) || in_array($sortBy, ['id', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        }

        return $query->paginate($perPage);
    }


}
