<?php

namespace App\Http\Controllers;

use App\Clinic;
use App\Helpers\Pagination;
use App\Helpers\Tracker;
use App\User;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $p = new Pagination($request);
        $query = Clinic::orderBy($p->orderBy,$p->ascOrdesc);

        $total = $query->count();
        $clinicList = $query->skip($p->skip)->take($p->rows)->get();

        return response([
            'items' => $clinicList,
            'total' => $total,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request,[
            'name'=>'required',
            'address'=>'required',
        ]);
        $request->merge(['slug'=>'C' . time()]);

        $clinic = Clinic::create($request->all());
        return response($clinic);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function show(Clinic $clinic)
    {
        return response($clinic);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function edit(Clinic $clinic)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Clinic $clinic)
    {
        $clinic->update($request->only([
            'name',
            'address',
            'link',
            'description',
        ]));

        return response($clinic);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Clinic  $clinic
     * @return \Illuminate\Http\Response
     */
    public function destroy(Clinic $clinic)
    {
        //
    }


    /**診所管理員 */
    public function getManagers($slug){
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        $managers = $clinic->managers()->get();
        return response($managers);
    }

    /**新增診所管理員 */
    public function addManager(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $clinic = Clinic::where('slug',$slug)->firstOrFail();

        if(!$result = $user->becomeRole('clinic_manager')){
            return response('error',400);
        }

        if(!$manager = $clinic->managers()->find($user->id)){
            $clinic->managers()->attach($user->id);
        }

        return response('success');
    }

    /**移除診所管理員 */
    public function removeManager(Request $request,$slug){
        Tracker::log($request);
        
        $user = User::findOrFail($request->user_id);
        $clinic = Clinic::where('slug',$slug)->firstOrFail();
        
        $clinic->managers()->detach($user->id);
        if(count($user->clinics()->get()) == 0){
            $user->removeRole('clinic_manager');
        }

        return response('success');
    }


}
