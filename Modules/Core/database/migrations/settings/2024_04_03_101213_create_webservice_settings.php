<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('webservice.base_url', 'https://juba.strathmore.edu/dataservice');
        $this->migrator->add('webservice.staff_endpoint', 'staff/getStaff/{staff_number}');
        $this->migrator->add('webservice.staff_by_username_endpoint', 'staff/getStaffByUsername/{username}');
        $this->migrator->add('webservice.student_endpoint', 'student/getStudent/{studentNumber}');
        $this->migrator->add('webservice.all_staff_endpoint', 'staff/getAllStaff');
        $this->migrator->add('webservice.all_current_students_endpoint', 'student/getAllCurrentStudents');
        $this->migrator->add('webservice.all_active_students_endpoint', 'student/getAllStudentsWithOpenAccounts');
        $this->migrator->add('webservice.all_departments_endpoint', 'department/getAllDepartments');
    }

    public function down(): void
    {
        $this->migrator->deleteIfExists('webservice.base_url');
        $this->migrator->deleteIfExists('webservice.staff_endpoint');
        $this->migrator->deleteIfExists('webservice.staff_by_username_endpoint');
        $this->migrator->deleteIfExists('webservice.student_endpoint');
        $this->migrator->deleteIfExists('webservice.all_staff_endpoint');
        $this->migrator->deleteIfExists('webservice.all_current_students_endpoint');
        $this->migrator->deleteIfExists('webservice.all_active_students_endpoint');
        $this->migrator->deleteIfExists('webservice.all_departments_endpoint');
    }
};
