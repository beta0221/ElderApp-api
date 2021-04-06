<?php

namespace App\Console\Commands;

use App\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:event {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete event row and every related rows in every table.';


    private $db = [
        'event_certificates'=>'event_id',
        'event_manager'=>'event_id',
        'event_users'=>'event_id',
        'frequently_event_user'=>'event_id',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $slug = $this->argument('slug');

        if(!$event = Event::where('slug',$slug)->first()){
            $this->info('Event not found');
            return;
        }


        foreach ($this->db as $table => $column) {
            if($row = DB::table($table)->where($column,$event->id)->first()){
                $this->info('Delete Fail:Event has related row in table ' . $table);
                return;
            }
        }

        DB::table('upload_event_body_image_logs')->where('slug',$slug)->delete();

        $imagePath = "/events/$slug/";
        if(Storage::disk('ftp')->exists($imagePath)){
            $result = Storage::disk('ftp')->deleteDirectory($imagePath);
            if(!$result){
                $this->info("Delete Fail:error occured while delete directory '$imagePath' .");
                return;
            }
        }

        $bodyImagePath = "/eventContent/$slug/";
        if(Storage::disk('ftp')->exists($bodyImagePath)){
            $result = Storage::disk('ftp')->deleteDirectory($bodyImagePath);
            if(!$result){
                $this->info("Delete Fail:error occured while delete directory '$bodyImagePath' .");
                return;
            }
        }


        $this->info('Delete Success:Delete event id ' . $event->id);
        $event->delete();

    }
}
