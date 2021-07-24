<?php

namespace App\Http\Controllers;

use App\Application;
use App\ApplicationDetail;
use App\Component;
use App\models\Warehouse;
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

    //material_id = xomashyo, $quantity = miqdori
    public function getMaterials($material_id, $necessary_quantity)
    {
        $current_quantity = 0;
        $result = array(
            'warehouse_id'=>null,
            'material_id'=>null,
            'taken_quantity'=>null,
            'price'=>null,
        );

        $materials_in_warehouse = Warehouse::where(['material_id' => $material_id])->get();

        /*ombordagi kerakli xomashyolarni chiqarib olamiz*/
        foreach ($materials_in_warehouse as &$material) {

            if($current_quantity == $necessary_quantity) {
                continue;
            }

            /*agar bazada biz soragan qatorda yetarli miqdor topilmasa*/
            if ($material->remainder < $necessary_quantity) {
                $current_quantity += $material->remainder;
                $material->remainder=0;
                $result['warehouse_id'] =$material->id;
                $result['material_id'] =$material->material_id;
                $result['taken_quantity'] =$current_quantity;
                $result['price'] =$material->price;

            } elseif ($material->remainder >= $necessary_quantity) {
                // kerakli miqdorni ajratib olamiz, oldin olingan bolsa, uni hisobga olamiz
                $quantity_to_be_taken = $necessary_quantity - $current_quantity;
                $current_quantity+=$quantity_to_be_taken;
                $material->remainder-=$quantity_to_be_taken;

                $result['warehouse_id'] =$material->id;
                $result['material_id'] =$material->material_id;
                $result['taken_quantity'] =$quantity_to_be_taken;
                $result['price'] =$material->price;
            }
        }
            return $result;
    }













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
