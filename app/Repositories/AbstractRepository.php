<?php

namespace App\Repositories;

use App\Interfaces\DTOInterface;
use App\Interfaces\RepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;

abstract class AbstractRepository implements RepositoryInterface
{
    protected static string $model;

    public function getAll(?array $filter = null): array
    {
        return self::getModel()
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    foreach ($filter as $key => $value) {
                        $query->where($key, $value);
                    }
                }
            })->get()->all();
    }

    public function getAllWithPagination(int $totalPerPage = 5, ?array $filter = null): LengthAwarePaginator
    {
        return self::getModel()
            ->where(function ($query) use ($filter) {
                if ($filter) {
                    foreach ($filter as $key => $value) {
                        $query->where($key, $value);
                    }
                }
            })->paginate(
                perPage: $totalPerPage,
                pageName: 'page'
            );
    }

    public function findOne(string|int $id): ?Model
    {
        return self::getModel()->find(intval($id));
    }

    public function store(DTOInterface $dto): Model
    {
        return self::getModel()
            ->create($dto->toArray());
    }

    public function update(DTOInterface $dto): Model|bool
    {
        $id = $dto->id ?? null;
        if (!$id) return false;

        if ($model = $this->findOne($id)) {
            foreach ($dto->toArray() as $field => $value) {
                $model->{$field} = $value;
            }
            if (!$model->save()) return false;

            return $model->refresh();
        }

        return false;
    }

    public function delete(string|int $id): void
    {
        if($model = $this->findOne($id)) 
            $model->delete();
    }


    protected static function getModel(): Model
    {
        return app(static::$model);
    }
}