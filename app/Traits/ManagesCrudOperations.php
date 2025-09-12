<?php

namespace App\Traits;

use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
use App\Events\UserActivityViewedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

trait ManagesCrudOperations
{
    /**
     * Create a new model instance.
     */
    protected function createModel(Model $model, Request $request, array $rules = [], array $additionalData = []): Model
    {
        $validated = $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $modelData = array_merge($validated, $additionalData);
            $createdModel = $model->create($modelData);
            
            // Log the creation event
            event(new UserActivityCreatedEvent(
                $createdModel, 
                Auth::user(),
                ['request_data' => $request->all()]
            ));
            
            DB::commit();
            
            return $createdModel;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create model', [
                'model' => get_class($model),
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            throw $e;
        }
    }

    /**
     * Update an existing model instance.
     */
    protected function updateModel(Model $model, Request $request, array $rules = [], array $additionalData = []): Model
    {
        $validated = $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $originalData = $model->getOriginal();
            $modelData = array_merge($validated, $additionalData);
            
            $model->update($modelData);
            
            // Log the update event
            event(new UserActivityUpdatedEvent(
                $model, 
                Auth::user(),
                $originalData,
                $model->getChanges(),
                ['request_data' => $request->all()]
            ));
            
            DB::commit();
            
            return $model->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update model', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete a model instance.
     */
    protected function deleteModel(Model $model, array $metadata = []): bool
    {
        try {
            DB::beginTransaction();
            
            $originalData = $model->getOriginal();
            
            // Log the deletion event before deleting
            event(new UserActivityDeletedEvent(
                $model, 
                Auth::user(),
                $originalData,
                $metadata
            ));
            
            $deleted = $model->delete();
            
            DB::commit();
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete model', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * View a model instance.
     */
    protected function viewModel(Model $model, string $viewType = 'view', array $metadata = []): void
    {
        event(new UserActivityViewedEvent(
            $model, 
            Auth::user(),
            $viewType,
            $metadata
        ));
    }

    /**
     * Get paginated results with activity logging.
     */
    protected function getPaginatedResults(Model $model, Request $request, int $perPage = 15, array $with = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $model->with($with);
        
        // Apply filters if they exist
        if (method_exists($this, 'applyFilters')) {
            $query = $this->applyFilters($query, $request);
        }
        
        $results = $query->paginate($perPage);
        
        // Log the view event for the collection
        event(new UserActivityViewedEvent(
            $model, 
            Auth::user(),
            'index',
            ['total_results' => $results->total(), 'per_page' => $perPage]
        ));
        
        return $results;
    }

    /**
     * Handle bulk operations.
     */
    protected function handleBulkOperation(Request $request, string $operation, array $ids = []): array
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:' . $this->getModelTable() . ',id'
        ]);

        $ids = $validated['ids'] ?? $ids;
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($ids as $id) {
            try {
                $model = $this->getModelClass()::findOrFail($id);
                
                switch ($operation) {
                    case 'delete':
                        $this->deleteModel($model, ['bulk_operation' => true]);
                        break;
                    case 'update':
                        // Handle bulk update logic here
                        break;
                    default:
                        throw new \InvalidArgumentException("Unknown operation: {$operation}");
                }
                
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "ID {$id}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Get the model class for this controller.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the model table name.
     */
    protected function getModelTable(): string
    {
        return (new $this->getModelClass())->getTable();
    }

    /**
     * Get device information from request.
     */
    protected function getDeviceInfo(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        if (str_contains($userAgent, 'Mobile')) {
            return 'Mobile';
        } elseif (str_contains($userAgent, 'Tablet')) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    /**
     * Get browser information from request.
     */
    protected function getBrowserInfo(Request $request): string
    {
        $userAgent = $request->userAgent();
        
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }
}
