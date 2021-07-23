<?php

namespace App\Http\Controllers;

use App\Application;
use App\ApplicationDetail;
use App\Component;
use App\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page = $request->input('pagination')['page'];
        $itemsPerPage = $request->input('pagination')['itemsPerPage'];
        return Application::with('component')
                          ->with('supplier')
                          ->paginate($itemsPerPage == '-1' ? 1000000 : $itemsPerPage, ['*'], 'page name', $page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRef()
    {
        return['suppliers' => Supplier::get(),
                'components' => Component::get()];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Application::where('id', $id)->with('supplier')
        ->with('component')
        ->with('applicationDetails')
        ->first();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function edit(Application $application)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $form = $request->all();
            $model = Application::find($form['id']);
            if (!$model) {
                $model = new Application();
            }
            $model->supplier_id = $form['supplier_id'];
            $model->delivery_date = $form['delivery_date'];
            $model->comment = $form['comment'];
            $model->total = $form['total'];
            $model->save();

            $application_details = $form['details'];
            foreach ($application_details as $key => $detail_value) {
                $detail = ApplicationDetail::where('id', $detail_value['id'])->first();
                if (!$detail) {
                    $detail = new ApplicationDetail();
                }
                $detail->application_id = $model->id;
                $detail->item_id = $detail_value['item_id'];
                $detail->item_type_id = $detail_value['item_type_id'];
                $detail->item_count = $detail_value['item_count'];
                $detail->price = $detail_value['price'];
                $detail->discount = $detail_value['discount'];
                $detail->save();
            }
            DB::commit();
            return ['message' => 'Successfully saved!'];
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Application  $application
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Application::find($id)->delete();
        ApplicationDetail::where('application_id', $id)->delete();
    }
}