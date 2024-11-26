<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Models\Pengaduan;
use App\Models\Tanggapan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;


class TanggapanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $pengaduan = Pengaduan::with('user')->find($request->pengaduan_id);
        $user = $pengaduan->user;
        $data = [
            'name' => 'Alifkhi', // customize with recipient's name or other data
            'message' => 'This is a test email sent from Laravel.'
        ];

        Mail::send([], $data, function ($message) use ($user,$pengaduan) {
            $message->to($user->email, $user->name)
                    ->subject('Tanggapan Laporan - '.$pengaduan->status)
                    ->setBody('<h2>Pengaduan Anda</h2><br>Nama : '.$user->name.'<br>Pengaduan : '.$pengaduan->description.'<br>Status : '.$pengaduan->status, 'text/html'); // HTML email content

            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });

        if (Mail::failures()) {
            Alert::error("Failed Send Email");
            return redirect('admin/pengaduans');
        }

        $pengaduan->update([
            'status'=> $request->status,
        ]);

        $petugas_id = Auth::user()->id;

        $data = $request->all();

        $data['pengaduan_id'] = $request->pengaduan_id;
        $data['petugas_id']=$petugas_id;

        Alert::success('Berhasil', 'Pengaduan berhasil ditanggapi');
        Tanggapan::create($data);
        return redirect('admin/pengaduans');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $item = Pengaduan::with([
            'details', 'user'
        ])->findOrFail($id);

        return view('pages.admin.tanggapan.add',[
            'item' => $item
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

    }

    public function sendEmail($toEmail, $subject, $message)
    {
        $data = [
            'message' => $message,
        ];

        Mail::send('emails.notification', $data, function($message) use ($toEmail, $subject) {
            $message->to($toEmail)
                    ->subject($subject);
        });
    }
}
