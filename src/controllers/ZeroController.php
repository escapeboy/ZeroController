<?php namespace ZeroController\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use ZeroController\Interfaces\Response;
use ZeroController\Models\ZeroModel;
use ZeroController\Requests\ZeroRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use ZeroController\Responses\ZeroResponse;

class ZeroController extends Controller
{
    /** @var string $list_view */
    public $list_view;
    /** @var string $form_view */
    public $form_view;
    /** @var string $policy */
    public $policy;

    /** @var Model  */
    public $model;

    /** @var Response */
    public $response;
    /** @var Request */
    public $request;

    /** @var array  */
    public $data = [];

    public function __construct() {
        if(is_null($this->request)){
            $this->request = new ZeroRequest();
        }
        if(is_null($this->response)){
            $this->response = new ZeroResponse();
        }
        if(is_null($this->model)){
            $this->model = new ZeroModel();
        }
    }

    /**
     * @return JsonResponse|mixed
     */
    public function list()
    {
        $request = $this->request;
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

            return $this->zeroResponse()->json();
        }
        $this->data['items'] = $this->model->paginate($request->get('limit', 20));

        return $this->zeroResponse()->view($this->list_view);
    }

    /**
     * @return Response
     */
    public function zeroResponse(): Response
    {
        return new $this->response($this->data);
    }

    /**
     * @param int $item
     * @return JsonResponse|mixed
     */
    public function get(int $item)
    {
        /** @var Model $item */
        $item = $this->model->findOrFail($item);

        $this->authorize($this->policy . 'view', $item);
        $request = $this->request;
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
            return $this->zeroResponse()->json();
        }
        return $this->zeroResponse()->view($this->form_view);
    }

    /**
     * @param int|null $item
     * @return RedirectResponse|JsonResponse|RedirectResponse
     */
    public function post(Request $request, int $item = null)
    {
        $request = $this->request;
        try {
            if (!$item) {
                $this->authorize($this->policy . 'create', $this->model);
                $item = $this->model;
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

            return $this->zeroResponse()->redirect()->back()->withInput()->withException($e);
        }
        if ($request->wantsJson()) {
            $this->data['item'] = $item;

            return $this->zeroResponse()->json();
        }

        return $this->zeroResponse()->redirect()->back()->with('message', 'Item Saved');
    }

    /**
     * @param int $item
     * @return RedirectResponse|JsonResponse|RedirectResponse
     */
    public function delete(int $item)
    {
        /** @var Model $item */
        $item = $this->model->firstOrFail($item);
        $this->authorize($this->policy . 'delete', $item);
        $request = $this->request;
        try {
            $item->delete();
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'code'  => $e->getCode(),
                ], 400);
            }

            return $this->zeroResponse()->redirect()->back()->withInput()->withException($e);
        }
        if ($request->wantsJson()) {
            $this->data = ['message' => 'Item Removed'];

            return $this->zeroResponse()->json();
        }

        return $this->zeroResponse()->redirect()->back()->with('message', 'Item Removed');
    }

    /**
     * @param int    $item
     * @param string $relation
     * @return JsonResponse
     * @throws \Exception
     */
    public function getRelation(int $item, string $relation): JsonResponse
    {
        /** @var Model $item */
        $item = $this->model->findOrFail($item);
        if (in_array($relation, $item->getRelationships())) {
            return response()->json($item->{$relation});
        }

        throw new \Exception('Relation "' . $relation . '" not found in ' . $item->reflection()->getName(), 400);
    }
}
