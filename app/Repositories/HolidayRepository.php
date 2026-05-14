<?php

namespace App\Repositories;

use App\Models\Holiday;

class HolidayRepository
{
    private $model;

    public function __construct(Holiday $model)
    {
        $this->model = $model;
    }

    public function getList($search = null, int $page = 1, int $perPage = 10)
    {
        $query = $this->model->query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getDetail($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $holiday = $this->model->findOrFail($id);
        $holiday->update($data);
        return $holiday;
    }

    public function delete($id)
    {
        $holiday = $this->model->findOrFail($id);
        $holiday->delete();
        return true;
    }

    public function isHoliday($date)
    {
        return $this->model->where('date', $date)->exists();
    }

}