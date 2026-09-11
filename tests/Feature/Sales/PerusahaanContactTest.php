<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\PicController;
use App\Models\Contact;
use App\Models\Perusahaan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PerusahaanContactTest extends TestCase
{
    public function test_company_contact_feature_exposes_controllers_routes_and_views(): void
    {
        $this->assertTrue(class_exists(ContactController::class));
        $this->assertTrue(class_exists(PicController::class));
        $this->assertTrue(Route::has('index.contact'));
        $this->assertTrue(Route::has('store.contact'));
        $this->assertTrue(Route::has('update.contact'));
        $this->assertTrue(Route::has('delete.contact'));
        $this->assertTrue(Route::has('index.pic'));
        $this->assertTrue(Route::has('store.pic'));
        $this->assertTrue(View::exists('crm.contact.index'));
        $this->assertTrue(View::exists('crm.pic.index'));
    }

    public function test_company_model_supports_sales_scoping_and_contact_relations(): void
    {
        $company = new Perusahaan(['nama_perusahaan' => 'PT Contoh', 'sales_key' => 'SLS001']);

        $this->assertSame('PT Contoh', $company->nama_perusahaan);
        $this->assertSame('SLS001', $company->sales_key);
        $this->assertTrue($company->canBeTransferred());
        $this->assertInstanceOf(HasMany::class, $company->contacts());
        $this->assertSame(Contact::class, $company->contacts()->getRelated()::class);
        $this->assertSame('id_perusahaan', $company->contacts()->getForeignKeyName());
    }

    public function test_contact_model_belongs_to_company(): void
    {
        $contact = new Contact;

        $this->assertInstanceOf(BelongsTo::class, $contact->perusahaan());
        $this->assertSame(Perusahaan::class, $contact->perusahaan()->getRelated()::class);
    }
}