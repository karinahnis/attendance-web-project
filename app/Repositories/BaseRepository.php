<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Str;

abstract class BaseRepository
{
    /**
     * @var Model|Builder
     */
    protected $model;

    public function getModel(): string
    {
        return $this->model;
    }

    public function findById(mixed $id, array $columns = ['*'], mixed $relations = null): mixed
    {
        if (!is_null($relations)) {
            $obj = $this->model::with($relations)->find($id, $columns);
        } else {
            $obj = $this->model::find($id, $columns);
        }
        if (is_null($obj)) {
            throw new RecordsNotFoundException(Str::snake(class_basename($this->model)) . ' not found');
        }
        return $obj;
    }

    public function findBy(mixed $id, string $column, mixed $relations = null): mixed
    {
        if (!is_null($relations)) {
            $obj = $this->model::with($relations)->where($column, "=", $id)->get();
        } else {
            $obj = $this->model::where($column, "=", $id)->get();
        }
        if (is_null($obj)) {
            throw new RecordsNotFoundException(Str::snake(class_basename($this->model)) . ' not found');
        }
        return $obj;
    }

    public function getAll(int $paginating = null): mixed
    {
        if ($paginating) {
            return $this->model::paginate($paginating);
        }
        return $this->model::get();
    }

    public function create($data, callable $onSuccess = null)
    {
        $model = $this->model::create($data);
        if (!is_null($model) && !is_null($onSuccess)) $onSuccess($model);
        return $model;
    }

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data): mixed
    {
        return $this->model::where('id', $id)->update($data);
    }

    /**
     * @param $id
     * @return int
     */
    public function delete($id): int
    {
        return $this->model::where('id', $id)->delete($id);
    }

    /**
     * Apply a simple search across multiple columns.
     *
     * @param Builder $query
     * @param string $term
     * @param array $columns
     * @return void
     */
    protected function applySearch(Builder $query, string $term, array $columns): void
    {
        $query->where(function (Builder $q) use ($term, $columns) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, 'like', '%' . $term . '%');
                } else {
                    $q->orWhere($column, 'like', '%' . $term . '%');
                }
            }
        });
    }

    /**
     * Apply sorting to a query. Optionally restrict to allowed columns.
     *
     * @param Builder $query
     * @param string $sortBy
     * @param string $sortOrder
     * @param array $allowed
     * @return void
     */
    protected function applySorting(Builder $query, string $sortBy, string $sortOrder = 'desc', array $allowed = []): void
    {
        if (!empty($allowed) && !in_array($sortBy, $allowed, true)) {
            $sortBy = $allowed[0];
        }

        // Fallback protection: ensure column is not empty
        if (empty($sortBy)) {
            $sortBy = 'id';
        }

        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
    }

    /**
     * Apply dynamic filters and return paginated results with meta.
     *
     * @param Builder $query
     * @param array $filters Filter mappings (key => callable)
     * @return array Contains 'data' and 'meta' keys
     */
    protected function applyFiltersAndPaginate(Builder $query, array $filters): array
    {
        // Apply filters dynamically
        foreach ($filters as $key => $callback) {
            if ($value = request($key)) {
                $callback($query, $value);
            }
        }

        $this->applySorting(
            $query,
            request('sort_by', 'created_at'),
            request('sort_order', 'desc')
        );

        $perPage = request('per_page', 15);
        $paginator = request()->boolean('simple')
            ? $query->simplePaginate($perPage)
            : $query->paginate($perPage);

        return [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                    ? $paginator->total()
                    : null,
                'last_page' => $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                    ? $paginator->lastPage()
                    : null,
            ],
        ];
    }
}
