
https://docs.google.com/spreadsheets/d/14KEnPMj0aj2NuoIAe0TlkqPfMyMNZYV5rOYJYD-_VEQ/edit#gid=0

php artisan make:model config -m
php artisan make:model service -m

php artisan make:model card_authorization -m

php artisan make:model family_friend_assignment -m

php artisan make:model invoice -m

php artisan make:model service_application -m 

php artisan make:model staff_assignment -m 

php artisan make:model transaction -m 


composer install
php artisan migrate:refresh --seed
composer dump-autoload
php artisan key:generate
php artisan passport:install

admin login  : superadmin@admin.com/lloyd@321




Needed RockGarden Admin API Endpoints:
-----------------------------------

User Roles and Permissions Mgt. Module:
•	Role and Access Rights (CRUD)
•	User (CRUD)
•	Authentication
•	Action Logs

Employee Record Module:
•	Employee (CRUD)
•	Attendance (CRUD) 
•	Roster (CRUD)

Care Plan Management Module:
•	Plan (CRUD)
•	Frequently Asked Question (CRUD)
•	Service Application (CRUD)
•	Employee/Staff Assignment (CRUD)
•	Staff Chart Entry Days (CRUD)
•	Staff Chart
	-- Client Activity (CRUD)
	-- Bath (CRUD)
	-- Bed Change (CRUD)
	-- Bowel Movement (CRUD)
	-- Food Intake (CRUD)
	-- Fluid Intake (CRUD)
	-- Cream (CRUD)
	-- Personal Care (CRUD)
	-- Client Medication (CRUD)
	-- Vital Signs (CRUD) 
	-- Covid-19 Check (CRUD) 
	-- Weight (CRUD) 
	-- Care Review Assessment (CRUD)
	-- Doctor’s Observation (CRUD)
	-- Wellbeing Check (CRUD)
	-- ABC Behavior (CRUD)
	-- Physiotherapy Session (CRUD)
	-- Hospital / Doctors Visit (CRUD)
	-- Fall Risk Assessment (CRUD)
	-- Movement Risk Assessment (CRUD)
	-- General Risk Assessment (CRUD)

Client, Family & Friend Module:
•	Client (RD)
•	Family & Friend Assignment (CRUD)

Billing and Invoice Module:
•	Invoice (CRUD)
•	Payment Transaction (RD)

EMAR Module:
•	Client Medication History
•	Vital Signs History
•	Covid-19 Status History
•	Weight History
•	Doctor’s Observation History

Inventory & Pharmacy Management Module:
• -Item (CRUD)
• -Item Group (CRUD)
• -Stock (CRUD)
• -Vendor (CRUD)
• -Sales Order (CRUD)
• -Purchase Order (CRUD)
• -Bill (CRUD)

Payroll Module:
•	Employee Salary (CRUD)
•	Employee Reimbursement (CRUD)
•	Employee Payslip (CRUD)
•	Employee Loan (CRUD)
•	PayRuns (CRUD)
•	Settings (PayRoll type, Tax Deduction, Allowance, Load Type)

Report Module:
•	Client Summary Report
•	Employee Summary Report
•	Sales Report
•	Purchase Summary Report
•	Stock Summary Report
•	Invoice Report
•	Transactions Report
•	Inventory (Profit & Loss) Report
•	Salary (Montly Statement) Report


{{base_url}}admin/get-roles




$messages = [
    'title.required' => 'Title required',
    'description.required' => 'Description required',
];

$validator = Validator::make($request->all(), [
    'title'         => 'required',
    'description'   => 'required',
    'attachment_one'=> 'image|mimes:jpeg,png,jpg',
    'attachment_two'=> 'image|mimes:jpeg,png,jpg',
],$messages);

if ($validator->fails()) {
   return $this->sendError($validator->errors()->first(), [],0);
}