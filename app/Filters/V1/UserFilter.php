<?php

declare(strict_types=1);

namespace App\Filters\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserFilter
{
    protected Request $request;
    protected Builder $query;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query, bool $isAdmin): Builder
    {
        $this->query = $query;

        if (!$isAdmin) {
            $this->query->where('status', 'active');
        }

        if ($isAdmin) {
            $this->filterByRole()
                ->filterByStatus();
        }

        return $this->filterByName()
            ->filterByEmail()
            ->filterByCreatedDate()
            ->filterBySearch()
            ->sortBy()
            ->query;
    }

    protected function filterByRole(): self
    {
        if ($this->request->has('role')) {
            $role = trim($this->request->role, '"');
            $this->query->where('role', $role);
        }
        return $this;
    }

    protected function filterByStatus(): self
    {
        if ($this->request->has('status')) {
            $this->query->where('status', $this->request->status);
        }
        return $this;
    }

    protected function filterByName(): self
    {
        if ($this->request->has('name')) {
            $this->query->where('name', 'like', '%' . $this->request->name . '%');
        }
        return $this;
    }

    protected function filterByEmail(): self
    {
        if ($this->request->has('email')) {
            $this->query->where('email', 'like', '%' . $this->request->email . '%');
        }
        return $this;
    }

    protected function filterByCreatedDate(): self
    {
        if ($this->request->has('created_from')) {
            $this->query->whereDate('created_at', '>=', $this->request->created_from);
        }
        if ($this->request->has('created_to')) {
            $this->query->whereDate('created_at', '<=', $this->request->created_to);
        }
        return $this;
    }

    protected function filterBySearch(): self
    {
        if ($this->request->has('search')) {
            $searchTerm = $this->request->search;
            $this->query->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        return $this;
    }

    protected function sortBy(): self
    {
        if ($this->request->has('sort_by')) {
            $sortField = $this->request->sort_by;
            $sortOrder = $this->request->input('order', 'asc');
            
            // Allowed Fields
            $allowedSortFields = ['name', 'email', 'created_at', 'status', 'role'];
            
            if (in_array($sortField, $allowedSortFields)) {
                $this->query->orderBy($sortField, $sortOrder);
            }
        } else {
            $this->query->latest('created_at');
        }
        
        return $this;
    }
}