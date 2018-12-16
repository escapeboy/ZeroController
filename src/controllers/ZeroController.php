<?php namespace ZeroController\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use ZeroController\Models\ZeroModel as CustomModel;
use ZeroController\Responses\ZeroResponse as CustomResponse;
use ZeroController\Requests\ZeroRequest as CustomRequest;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ZeroController extends Controller
{
    /** @var string $list_view */
    public $list_view;
    /** @var string $form_view */
    public $form_view;
    /** @var string $policy */
    public $policy;
    /** @var Model */
    public $model;
    /** @var string $response */
    public $response = 'ZeroController\Responses\ZeroResponse';
    /** @var string $request */
    public $request = 'ZeroController\Requests\ZeroRequest';
    /** @var array $data */
    public $data = [];

    public function __construct()
    {
        class_alias($this->request, 'CustomRequest');
        class_alias($this->response, 'CustomResponse');
        class_alias($this->model, 'CustomModel');
    }

    /**
     * @param Request $request
     * @return View|JsonResponse
     */
    public function list(CustomRequest $request)
    {
        $this->model = new $this->model;
        if ($request->has('with')) {
            $this->model = $this->model->with($request->get('with'));
        }
        if ($request->has('appends')) {
            $this->model->setAppends($request->get('appends'));
        }
        if ($request->has('fields')) {
            $this->model = $this->model->select($request->get('fields'));
        }
        if ($request->has('filter')) {
            $this->model = $this->model->where($request->get('filter'));
        }
        if ($request->wantsJson()) {
            $this->data = $this->model->simplePaginate($request->get('limit', 20));

            return $this->response()->json();
        }
        $this->data['items'] = $this->model->paginate($request->get('limit', 20));

        return $this->response()->view($this->list_view);
    }

    /**
     * @return CustomResponse
     */
    public function response(): CustomResponse
    {
        return new CustomResponse($this->data);
    }

    /**
     * @param CustomRequest $request
     * @param CustomModel   $item
     * @return View|JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function get(CustomRequest $request, CustomModel $item)
    {
        $this->authorize($this->policy . 'view', $item);
        if ($request->has('with')) {
            $item->loadMissing($request->get('with'));
        }

        if ($request->has('appends')) {
            $item->setAppends($request->get('appends'));
        }
        if ($request->has('fields')) {
            $item = $item->only($request->get('fields'));
        }
        $this->data['item'] = $item;
        if ($request->wantsJson()) {
            return $this->response()->json();
        }

        return $this->response()->view($this->form_view);
    }

    /**
     * @param CustomRequest    $request
     * @param null|CustomModel $item
     * @return RedirectResponse | JsonResponse
     */
    public function post(CustomRequest $request, ?CustomModel $item)
    {
        try {
            if (!$item) {
                $this->authorize($this->policy . 'create', CustomModel::class);
                $item = new CustomModel();
            }
            $this->authorize($this->policy . 'update', $item);

            $item->autoFill($request);

            foreach ($request->all() as $key => $relation) {
                if (in_array($key, $item->getRelationships())) {
                    $rel_data = [];
                    foreach ($relation as $data) {
                        $rel_data[array_keys($data)[0]] = array_values($data)[0];
                    }
                    $item->{$key}()->sync($rel_data);
                }
            }
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                ], 400);
            }

            return $this->response()->redirect()->back()->withInput()->withException($e);
        }
        if ($request->wantsJson()) {
            $this->data['item'] = $item;

            return $this->response()->json($this->data);
        }

        return $this->response()->redirect()->back()->with('message', 'Item Saved');
    }

    /**
     * @param CustomRequest $request
     * @param CustomModel   $item
     * @return RedirectResponse|JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(CustomRequest $request, CustomModel $item)
    {
        $this->authorize($this->policy . 'delete', $item);
        try {
            $item->delete();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                ], 400);
            }

            return $this->response()->redirect()->back()->withInput()->withException($e);
        }
        if ($request->wantsJson()) {
            $this->data = ['message' => 'Item Removed'];

            return $this->response()->json();
        }

        return $this->response()->redirect()->back()->with('message', 'Item Removed');
    }

    /**
     * @param CustomRequest $request
     * @param CustomModel   $item
     * @param               $relation
     * @return JsonResponse
     * @throws \Exception
     */
    public function getRelation(CustomRequest $request, CustomModel $item, $relation): JsonResponse
    {
        if (in_array($relation, $item->getRelationships())) {
            return response()->json($item->{$relation});
        }

        throw new \Exception('Relation "' . $relation . '" not found in ' . $item->reflection()->getName(), 400);
    }
}
