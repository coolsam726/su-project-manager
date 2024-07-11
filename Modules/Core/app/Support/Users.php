<?php

namespace Modules\Core\Support;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\Models\Ldap\Staff;
use Modules\Core\Models\Ldap\Student;
use Symfony\Component\Console\Command\Command;

class Users
{
    public static function make(): static
    {
        return new static();
    }

    /**
     * @throws RequestException
     * @throws Exception
     */
    public function syncUser(string $username, string $provider, ?bool $skipLdapImport = false): User
    {
        $sysbot = $sysbot = utils()->sysbot();
        if (! \auth()->check()) {
            Auth::loginUsingId($sysbot?->id);
        }
        if (! $skipLdapImport) {
            $res = Artisan::call('ldap:import', [
                'provider' => $provider,
                'user' => $username,
                '--no-interaction',
                '--restore' => true,
                '--delete' => true,
            ]);
            if ($res === Command::FAILURE) {
                throw new Exception('The import command failed.');
            } elseif ($res === Command::INVALID) {
                throw new Exception('The options given could not be used for the import.');
            }
        }

        $user = \App\Models\User::whereUsername($username)->first();
        if (! $user) {
            throw new Exception('Error: User not found in AD or could not be synchronized.');
        }

        $query = $provider === 'staff' ? Staff::query() : Student::query();

        $adUser = $query->where('samaccountname', '=', $username)->first();
        if ($adUser) {

            Log::info("User with username $username found in AD. Updating");

            $user->update([
                'guid' => $adUser->getConvertedGuid(),
                'uac' => $uac = $adUser->getFirstAttribute('useraccountcontrol'),
                'is_active' => intval($uac) === 512,
            ]);
        } else {
            Log::info("User with username $username not found in AD. Deactivating");
            $user->update([
                'is_active' => false,
            ]);
        }

        // Sync with DataService
        if ($provider === 'staff') {
            $staff = Webservice::make()->fetchStaffByUsername($username);
            /**
             * {
             * "payrollNo" => "3040"
             * "employeeNo" => "3040"
             * "username" => "smaosa"
             * "lastName" => "Maosa"
             * "firstName" => "Samson"
             * "middleName" => "Arosi"
             * "names" => "Maosa, Samson Arosi"
             * "genderId" => "1"
             * "gender" => "Male"
             * "categoryId" => "11"
             * "category" => "Administrative"
             * "jobStatusId" => "10"
             * "jobStatus" => "2 Year Contract"
             * "jobStatusType" => "ft"
             * "dateOfBirth" => "1989-10-20"
             * "departmentId" => "42"
             * "departmentShortName" => "ICTS"
             * "department" => "Information and Communication Technology Services"
             * "supervisorUsernames" => "mndeto"
             * "jobTitle" => "Assistant Manager, ICT Enterprise Application Services"
             * "email" => "smaosa@strathmore.edu"
             * "mobileNo" => "0708467001"
             * "mealsActive" => "1"
             * "mealsAllowance" => "4000"
             * "hodPayrollNo" => "315"
             * "hodUsername" => "smomanyi"
             * "delegatePayrollNo" => "1024"
             * "delegateUsername" => "bogutu"
             * }
             */
            if ($staff->count()) {
                // Update user
                $userNumber = $staff->get('employeeNo');
                $user->update(['code' => Str::padLeft($userNumber, 5, '0')]);
                // Update staff Profile
                $user->update(['user_number' => $userNumber, 'name' => implode(' ', [$staff->get('firstName'), $staff->get('middleName'), $staff->get('lastName')])]);
                /*$user->profile()->update([
                    'user_number' => $userNumber,
                    'first_name' => $staff->get('firstName'),
                    'middle_name' => $staff->get('middleName'),
                    'last_name' => $staff->get('lastName'),
                    'phone' => $staff->get('mobileNo'),
                    'email' => $staff->get('email'),
                    'gender' => $staff->get('gender'),
                    'dob' => ($dob = $staff->get('dateOfBirth')) ? Carbon::parse($dob) : null,
                    'department_short_name' => $staff->get('departmentShortName'),
                    'meals_active' => (bool) intval($staff->get('mealsActive')),
                    'meal_allowance' => floatval($staff->get('mealsAllowance')),
                ]);*/
            } else {
                /*$user->profile()->update([
                    'meals_active' => false,
                    'meal_allowance' => 0.0,
                ]);*/
            }
        } elseif ($provider === 'students') {
            $student = Webservice::make()->fetchStudent($username);
            Log::info('Student data: '.json_encode($student));
            // TODO: implement student sync
        }

        if (\auth()->user()?->id === $sysbot?->id) {
            Auth::logout();
        }

        return $user;
    }

    public static function ldapMasqueradable(): bool
    {
        return config('app.env') === 'local' && config('app.debug') && config('core.masquerade', false);
    }

    public function ldapMasquerade($username, string $credential_type = 'username'): bool|User
    {
        if (! static::ldapMasqueradable()) {
            return false;
        }
        if ($credential_type !== 'username') {
            $credential_type = 'email';
        }
        $user = User::where($credential_type, $username)->first();
        if (! $user) {
            abort(401, 'User not found');
        }
        if (! $user->is_active) {
            abort(401, 'The user is inactive');
        }
        Auth::login($user);

        return $user;
    }
}
