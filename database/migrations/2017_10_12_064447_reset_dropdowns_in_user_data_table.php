<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ResetDropdownsInUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('user_data', 'status') && Schema::hasColumn('user_data', 'assign_to')) {
            Schema::table('user_data', function (Blueprint $table) {

                $table->dropColumn('status');
                $table->dropColumn('purpose');
                $table->dropColumn('industry');
                $table->dropColumn('true_client');
                //$table->dropColumn('assign_to');
                $table->dropColumn('won/lost');

            });
        }
            if (Schema::hasColumn('user_data', 'salary')){
                Schema::table('user_data', function (Blueprint $table) {
                    $table->dropColumn('salary');

                    \DB::statement("DELETE FROM tabs where tab_name = 'ALL' ");
                    \DB::statement("INSERT INTO tabs (tab_name, query,created_at,updated_at)  
                                    VALUES ('All','SELECT username, firstname, lastname, email, contact,city,country, contact, source,status,purpose, industry, follow_up_date,true_client,utm_source,utm_campaign,reference,assign_to,deleted_at,created_at,updated_at FROM users',
                                    CURRENT_TIMESTAMP,CURRENT_TIMESTAMP )"
                                  );
                });
            }

            Schema::table('user_data', function (Blueprint $table) {

                $table->string('status',100)->nullable();
                $table->string('purpose',100)->nullable();
                $table->string('industry',100)->nullable();
                $table->string('true_client',100)->nullable();
                //$table->string('assign_to',100)->nullable();
                $table->string('won_or_lost',100)->nullable();
            });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_data', function (Blueprint $table) {

            $table->dropColumn('status');
            $table->dropColumn('purpose');
            $table->dropColumn('industry');
            $table->dropColumn('true_client');
            //$table->dropColumn('assign_to');
            $table->dropColumn('won_or_lost');
        });

        Schema::table('user_data', function (Blueprint $table) {
            $table->enum('status',['Targeted','Final_Stage','Untargeted','Reseller','Busy','Not_Reseller','DevAPI,Farzi','Startup','International'])->after('source');
            $table->enum('purpose',['Mix','Transactional','Promotional','SendOTP'])->after('source');
            $table->enum('industry',['Agriculture','Automobiles & Transport','B2B','E-Commerce','Education','Freelancer','IT Company','Stock & Commodity','RealEstate',
                'Travel','Sports','Gym','HealthCare','Hospitality','Retail & FMCG','Wholesale Distributor','Website Developer & Hosting','Media & Ads','Personal Use',
                'Religious','ERP/CRM','Political','Job Portal','Logistics','Event Management','Others'])->after('purpose');
            $table->enum('true_client',['True','Not True'])->after('source');;
            //$table->enum('assign_to',['Mona','Rahul_Verma','Jerry','Ravi','Chinmay'])->after('source');;
            $table->enum('won/lost',['Won','Lost_Pricing','Lost_Service','Lost_Feature','Lost_Not Required','Lost_Others'])->after('source');;

        });
    }
}
