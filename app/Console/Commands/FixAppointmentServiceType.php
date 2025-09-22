<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Service;

class FixAppointmentServiceType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:appointment-service-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all appointments so that service_type is set to the correct service name.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing appointment service_type values...');
        $count = 0;
        Appointment::chunk(100, function ($appointments) use (&$count) {
            foreach ($appointments as $appointment) {
                // Fix service_type if needed
                if ($appointment->service_id) {
                    $service = Service::find($appointment->service_id);
                    if ($service && $appointment->service_type !== $service->name) {
                        $appointment->service_type = $service->name;
                        $appointment->save();
                        $count++;
                    }
                }
                // Fix status if payment_status is paid but status is not completed
                if ($appointment->payment_status === 'paid' && $appointment->status !== 'completed') {
                    $appointment->status = 'completed';
                    $appointment->save();
                    $count++;
                }
            }
        });
        $this->info("Updated $count appointments (service_type and/or status).");
        $this->info('Done.');
        return 0;
    }
}
