<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_application', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('client_id')->nullable();
            $table->bigInteger('applicant_id')->nullable();
            $table->string('phone_number_payee',765)->nullable();
            $table->string('phone_number_next_of_kin',765)->nullable();
            $table->string('primary_language_spoken',765)->nullable();
            $table->tinyInteger('receiving_service_elsewhere',4)->nullable();
            $table->string('home_settings_description',765)->nullable();
            $table->tinyInteger('require_general_healthcare',4)->nullable();
            $table->tinyInteger('require_mobility_assistance',4)->nullable();
            $table->tinyInteger('require_personal_supervision',4)->nullable();
            $table->tinyInteger('require_emotional_support',4)->nullable();
            $table->tinyInteger('require_emotional_support',4)->nullable();
            $table->tinyInteger('require_demantia_care',4)->nullable();
            $table->tinyInteger('require_grocery_shopping_assistance',4)->nullable();
            $table->tinyInteger('require_feeding_assistance',4)->nullable();
            $table->tinyInteger('require_haircare_nailcare_assistance',4)->nullable();
            $table->tinyInteger('require_bathing_grooming_assistance',4)->nullable();

            $table->tinyInteger('require_dishes_laundry_assistance',4)->nullable();
            $table->tinyInteger('require_meal_prep_assistance',4)->nullable();
            $table->tinyInteger('require_toileting_assistance',4)->nullable();
            $table->tinyInteger('require_health_monitoring',4)->nullable();
            $table->tinyInteger('require_vital_signs_monitoring',4)->nullable();
            $table->tinyInteger('require_oral_skin_medication',4)->nullable();
            $table->tinyInteger('require_injections',4)->nullable();
            $table->tinyInteger('require_dressing_of_wounds',4)->nullable();
            $table->tinyInteger('require_oxygen_therapy',4)->nullable();
            $table->tinyInteger('require_exercise_oral_feeding',4)->nullable();
            $table->tinyInteger('require_ng_tube_feeding',4)->nullable();
            $table->tinyInteger('require_post_surgical_management',4)->nullable();
            $table->tinyInteger('require_companionship',4)->nullable();
            $table->tinyInteger('require_appointment_reminder',4)->nullable();
            $table->tinyInteger('require_patient_recovery_monitoring',4)->nullable();
            $table->tinyInteger('require_improvement_suggestions',4)->nullable();
            $table->tinyInteger('require_improvement_advice',4)->nullable();
            $table->tinyInteger('require_steady_availability_for_questions',4)->nullable();
            $table->tinyInteger('require_highly_skilled_nursing',4)->nullable();
            $table->tinyInteger('require_other_skilled_nursing',4)->nullable();
            $table->tinyInteger('require_other_assistance',4)->nullable();

            $table->string('other_assistance_description',765)->nullable();
            $table->string('main_source_of_finance',765)->nullable();

            $table->tinyInteger('has_history_of_urinary_incontinence',4)->nullable();
            $table->tinyInteger('has_history_of_feacal_incontinence',4)->nullable();
            $table->tinyInteger('number_of_falls_past_12months',4)->nullable();
            $table->tinyInteger('has_diabetes',4)->nullable();
            $table->tinyInteger('has_hypertension',4)->nullable();
            $table->tinyInteger('has_hearing_impairment',4)->nullable();
            $table->tinyInteger('has_dental_problem',4)->nullable();
            $table->tinyInteger('has_stroke_tia',4)->nullable();
            $table->tinyInteger('has_sleep_problem',4)->nullable();
            $table->tinyInteger('has_arthritis',4)->nullable();
            $table->tinyInteger('has_difficulty_moving_around',4)->nullable();
            $table->tinyInteger('has_blindness_or_partial',4)->nullable();
            $table->tinyInteger('has_congestive_heart_failure',4)->nullable();
            $table->tinyInteger('has_history_of_demantia',4)->nullable();
            $table->tinyInteger('has_history_of_mental_illness',4)->nullable();
            $table->tinyInteger('has_cancer_or_terminal_illness',4)->nullable();

            $table->string('other_health_problems',765)->nullable();
            $table->bigInteger('admissions_in_last_1year')->nullable();

            $table->string('past_medical_surgical_history',765)->nullable();
            $table->string('all_current_medications',765)->nullable();
            $table->string('known_allergies',765)->nullable();

            $table->double('weight_kg', 16, 2)->nullable();
            $table->double('height_ft', 16, 2)->nullable();


            $table->string('build_slim_or_plum',765)->nullable();
            $table->string('latest_blood_pressure',765)->nullable();
            $table->string('latest_fasting_blood_sugar',765)->nullable();
            $table->string('hiv_status',765)->nullable();
            $table->string('hbsag_hcv_status',765)->nullable();
            $table->string('all_relevant_diagnosis',765)->nullable();
            $table->text('signature')->nullable();
            $table->tinyInteger('send_all_correspondence_to_applicant',4)->nullable();

            $table->double('service_cost', 16, 2)->nullable();

            $table->date('initial_payment_date')->nullable();
            $table->date('date_approved')->nullable();
            $table->tinyInteger('is_approved',4)->nullable();

            $table->string('fullname_next_of_kin',765)->nullable();
            $table->string('fullname_signatory',765)->nullable();
            $table->string('require_basic_food_preparation',765)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_application');
    }
}