<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'employee_id' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@company.com',
                'phone' => '+1234567890',
                'department' => 'IT',
                'position' => 'Software Developer',
                'hire_date' => '2023-01-15',
                'status' => 'active',
            ],
            [
                'employee_id' => 'EMP002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@company.com',
                'phone' => '+1234567891',
                'department' => 'HR',
                'position' => 'HR Manager',
                'hire_date' => '2022-03-20',
                'status' => 'active',
            ],
            [
                'employee_id' => 'EMP003',
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@company.com',
                'phone' => '+1234567892',
                'department' => 'Finance',
                'position' => 'Financial Analyst',
                'hire_date' => '2023-06-10',
                'status' => 'active',
            ],
            [
                'employee_id' => 'EMP004',
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@company.com',
                'phone' => '+1234567893',
                'department' => 'Marketing',
                'position' => 'Marketing Specialist',
                'hire_date' => '2023-02-28',
                'status' => 'active',
            ],
            [
                'employee_id' => 'EMP005',
                'first_name' => 'David',
                'last_name' => 'Brown',
                'email' => 'david.brown@company.com',
                'phone' => '+1234567894',
                'department' => 'IT',
                'position' => 'System Administrator',
                'hire_date' => '2022-11-05',
                'status' => 'active',
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::create($employeeData);
        }
    }
}
