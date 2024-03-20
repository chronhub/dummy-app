<?php

declare(strict_types=1);

namespace App\Chron\Projection\ReadModel;

use App\Chron\Model\Customer\Event\CustomerRegistered;
use App\Chron\Model\Customer\Gender;
use Illuminate\Database\Schema\Blueprint;
use Storm\Projector\Support\ReadModel\InteractWithStack;

final class CustomerReadModel extends ReadModelConnection
{
    public const string TABLE = 'read_customer';

    use InteractWithStack;

    protected function insert(CustomerRegistered $event): void
    {
        $this->query()->insert([
            'id' => $event->aggregateId()->toString(),
            'email' => $event->email()->value,
            'name' => $event->name()->value,
            'gender' => $event->gender()->value,
            'birthday' => $event->birthday()->value,
            'phone_number' => $event->phoneNumber()->value,
            'street' => $event->address()->street,
            'city' => $event->address()->city,
            'postal_code' => $event->address()->postalCode,
            'country' => $event->address()->country,
        ]);
    }

    protected function updateEmail(string $id, string $email): void
    {
        $this->query()->where('id', $id)->update(['email' => $email]);
    }

    protected function up(): callable
    {
        return function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('gender', Gender::toStrings());
            $table->date('birthday');
            $table->string('email')->unique();
            $table->string('phone_number', 20); //unique
            $table->string('street');
            $table->string('city');
            $table->string('postal_code');
            $table->string('country');

            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('updated_at', 6)->nullable()->useCurrentOnUpdate();
        };
    }

    protected function tableName(): string
    {
        return self::TABLE;
    }
}
