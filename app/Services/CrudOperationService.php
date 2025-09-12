<?php

namespace App\Services;

use App\Events\UserActivityCreatedEvent;
use App\Events\UserActivityUpdatedEvent;
use App\Events\UserActivityDeletedEvent;
use App\Events\UserActivityViewedEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CrudOperationService
{
    protected UserActivityService $activityService;

    public function __construct(UserActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Create a new model instance with activity logging.
     */
    public function createModel(
        string $modelClass,
        Request $request,
        array $rules = [],
        array $additionalData = [],
        ?User $user = null
    ): Model {
        $user = $user ?? Auth::user();
        $validated = $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $modelData = array_merge($validated, $additionalData);
            $createdModel = $modelClass::create($modelData);
            
            // Log the creation event
            $this->activityService->logCrudOperation(
                'created',
                $createdModel,
                $user,
                [],
                [],
                ['request_data' => $request->all()]
            );
            
            DB::commit();
            
            return $createdModel;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create model', [
                'model' => $modelClass,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            throw $e;
        }
    }

    /**
     * Update an existing model instance with activity logging.
     */
    public function updateModel(
        Model $model,
        Request $request,
        array $rules = [],
        array $additionalData = [],
        ?User $user = null
    ): Model {
        $user = $user ?? Auth::user();
        $validated = $request->validate($rules);
        
        try {
            DB::beginTransaction();
            
            $originalData = $model->getOriginal();
            $modelData = array_merge($validated, $additionalData);
            
            $model->update($modelData);
            $updatedData = $model->getChanges();
            
            // Log the update event
            $this->activityService->logCrudOperation(
                'updated',
                $model,
                $user,
                $originalData,
                $updatedData,
                ['request_data' => $request->all()]
            );
            
            DB::commit();
            
            return $model->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to update model', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete a model instance with activity logging.
     */
    public function deleteModel(
        Model $model,
        array $metadata = [],
        ?User $user = null
    ): bool {
        $user = $user ?? Auth::user();
        
        try {
            DB::beginTransaction();
            
            $originalData = $model->getOriginal();
            
            // Log the deletion event before deleting
            $this->activityService->logCrudOperation(
                'deleted',
                $model,
                $user,
                $originalData,
                [],
                $metadata
            );
            
            $deleted = $model->delete();
            
            DB::commit();
            
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete model', [
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * View a model instance with activity logging.
     */
    public function viewModel(
        Model $model,
        string $viewType = 'view',
        array $metadata = [],
        ?User $user = null
    ): void {
        $user = $user ?? Auth::user();
        
        $this->activityService->logCrudOperation(
            'viewed',
            $model,
            $user,
            [],
            [],
            array_merge($metadata, ['view_type' => $viewType])
        );
    }

    /**
     * Get paginated results with activity logging.
     */
    public function getPaginatedResults(
        string $modelClass,
        Request $request,
        int $perPage = 15,
        array $with = [],
        ?User $user = null
    ): \Illuminate\Pagination\LengthAwarePaginator {
        $user = $user ?? Auth::user();
        $query = $modelClass::with($with);
        
        // Apply filters if they exist - can be overridden in controllers
        // if (method_exists($this, 'applyFilters')) {
        //     $query = $this->applyFilters($query, $request);
        // }
        
        $results = $query->paginate($perPage);
        
        // Log the view event for the collection
        $this->activityService->logCrudOperation(
            'viewed',
            new $modelClass(),
            $user,
            [],
            [],
            ['total_results' => $results->total(), 'per_page' => $perPage, 'view_type' => 'index']
        );
        
        return $results;
    }

    /**
     * Handle bulk operations with activity logging.
     */
    public function handleBulkOperation(
        string $modelClass,
        Request $request,
        string $operation,
        array $ids = [],
        ?User $user = null
    ): array {
        $user = $user ?? Auth::user();
        
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:' . (new $modelClass())->getTable() . ',id'
        ]);

        $ids = $validated['ids'] ?? $ids;
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($ids as $id) {
            try {
                $model = $modelClass::findOrFail($id);
                
                switch ($operation) {
                    case 'delete':
                        $this->deleteModel($model, ['bulk_operation' => true], $user);
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
     * Validate request data.
     */
    public function validateRequest(Request $request, array $rules): array
    {
        return $request->validate($rules);
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
