<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Link existing EOs to demo EO user
        $eoUser = DB::table('users')->where('email', 'eo@demo.com')->first();
        
        if ($eoUser) {
            // Link EventPro to EO user
            DB::table('event_organizers')->where('name', 'EventPro')->update([
                'user_id' => $eoUser->id,
                'is_active' => true
            ]);
            
            // Update user eo_id to EventPro
            DB::table('users')->where('id', $eoUser->id)->update([
                'eo_id' => DB::table('event_organizers')->where('name', 'EventPro')->value('id')
            ]);
        }
    }

    public function down(): void {
        DB::table('event_organizers')->update(['user_id' => null]);
    }
};