<?php

namespace Tests\Unit;

use App\Models\karyawan;
use App\Models\User;
use App\Notifications\GeneralAnnouncementNotification;
use Tests\TestCase;

/**
 * Tes tanpa database: semua model dibuat di memori dan tidak disimpan.
 */
class GeneralAnnouncementNotificationTest extends TestCase
{
    private function makeUser(?string $email): User
    {
        $user = new User(['username' => 'tester']);
        $user->setRelation('karyawan', new karyawan([
            'nama_lengkap' => 'Budi Tester',
            'email' => $email,
        ]));

        return $user;
    }

    private function makeNotification(): GeneralAnnouncementNotification
    {
        return new GeneralAnnouncementNotification(
            'Judul Tes',
            'Isi pengumuman tes.',
            '/home',
            'Pengumuman'
        );
    }

    public function test_via_includes_mail_when_email_is_valid(): void
    {
        $channels = $this->makeNotification()->via($this->makeUser('budi@kantor.co.id'));

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
    }

    public function test_via_skips_mail_when_email_is_empty_or_invalid(): void
    {
        foreach ([null, '', 'bukan-email'] as $email) {
            $channels = $this->makeNotification()->via($this->makeUser($email));

            $this->assertSame(['database'], $channels);
        }
    }

    public function test_route_notification_for_mail_returns_karyawan_email(): void
    {
        $this->assertSame(
            'budi@kantor.co.id',
            $this->makeUser('budi@kantor.co.id')->routeNotificationForMail()
        );
        $this->assertNull($this->makeUser('salah')->routeNotificationForMail());
    }

    public function test_to_mail_has_expected_content(): void
    {
        $mail = $this->makeNotification()->toMail($this->makeUser('budi@kantor.co.id'));

        $this->assertSame('[Pengumuman] Judul Tes', $mail->subject);
        $this->assertStringContainsString('Budi Tester', $mail->greeting);
        $this->assertContains('Isi pengumuman tes.', $mail->introLines);
        $this->assertStringEndsWith('/home', $mail->actionUrl);
    }

    public function test_to_array_matches_portal_notification_format(): void
    {
        $data = $this->makeNotification()->toArray($this->makeUser('budi@kantor.co.id'));

        $this->assertSame('unread', $data['status']);
        $this->assertSame('/home', $data['path']);
        $this->assertSame('Pengumuman', $data['message']['tipe']);
        $this->assertSame('Judul Tes', $data['message']['judul']);
    }

    public function test_to_array_preserves_sender_for_queued_notifications(): void
    {
        $notification = new GeneralAnnouncementNotification(
            'Judul Tes',
            'Isi pengumuman tes.',
            '/home',
            'Penting',
            'admin'
        );

        $data = $notification->toArray($this->makeUser('budi@kantor.co.id'));

        $this->assertSame('admin', $data['user']);
    }
}
