<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GetStream\StreamChat\Client as StreamClient;
use App\User;
use Illuminate\Support\Facades\Hash;

class InitializeChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'channel:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize channel with admin user';

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
        $user = new User;

        $user->username = 'mezie';
        $user->email = 'chimezie@adonismastery.com';
        $user->is_admin = true;
        $user->password = Hash::make('password');

        $user->save();

        $client = new StreamClient(
            env('MIX_STREAM_API_KEY'),
            env('MIX_STREAM_API_SECRET'),
            null,
            null,
            9
        );

        $client->updateUser([
            'id' => $user->username,
            'name' => $user->username,
            'role' => 'admin'
        ]);

        $channel = $client->Channel('messaging', 'Chatroom', [
            'created_by' => $user->username,
        ]);

        $channel->addMembers([$user->username]);

        return $this->info('Channel initialized');
    }
}
