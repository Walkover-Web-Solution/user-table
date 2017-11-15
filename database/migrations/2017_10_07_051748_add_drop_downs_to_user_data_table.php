<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDropDownsToUserDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {    if(Schema::hasColumn('user_data', 'status') && Schema::hasColumn('user_data', 'assign_to'))
    {
        Schema::table('user_data', function (Blueprint $table) {

            $table->dropColumn('status');
            $table->dropColumn('purpose');
            $table->dropColumn('industry');
            $table->dropColumn('true_client');
            //$table->dropColumn('assign_to');
        });
    }


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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        if(Schema::hasColumn('user_data', 'status') && Schema::hasColumn('user_data', 'assign_to'))
        {
            Schema::table('user_data', function (Blueprint $table) {

                $table->dropColumn('status');
                $table->dropColumn('purpose');
                $table->dropColumn('industry');
                $table->dropColumn('true_client');
                //$table->dropColumn('assign_to');
                $table->dropColumn('won/lost');
            });
        }
        Schema::table('user_data', function (Blueprint $table) {
            if(! (Schema::hasColumn('user_data', 'status')) ) ; //check whether users table has email column
            {
                $table->string('status',20)->nullable();
                $table->string('purpose',20)->nullable();
                $table->string('industry',20)->nullable();
                $table->boolean('true_client')->nullable();
                //$table->string('assign_to',50)->nullable();
            }
        });
    }
}
