<?php
include_once(__DIR__ . "/../BaseModel.php");

class UserModel extends BaseModel
{

    public function login($email, $password)
    {
        $user = $this->fetchOne("SELECT * FROM users WHERE email=? AND is_active=1", "s", [$email]);
        if ($user != null && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        $_SESSION['error']['login'] = "Invalid email or password";
        return false;
    }

    public function registerPatient($name, $email, $password, $phone, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone)
    {
        $old = $this->fetchOne("SELECT id FROM users WHERE email=?", "s", [$email]);
        if ($old != null) {
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->execute("INSERT INTO users (name, email, password_hash, phone, role) VALUES (?, ?, ?, ?, 'patient')", "ssss", [$name, $email, $hash, $phone]);
        $user_id = $this->getLastId();

        return $this->execute("INSERT INTO patients (user_id, date_of_birth, blood_group, gender, address, emergency_contact_name, emergency_contact_phone) VALUES (?, ?, ?, ?, ?, ?, ?)", "issssss", [$user_id, $dob, $blood, $gender, $address, $emergencyName, $emergencyPhone]);
    }

    public function getUser($id)
    {
        return $this->fetchOne("SELECT * FROM users WHERE id=?", "i", [$id]);
    }

    public function updateUserBasic($id, $name, $email, $phone, $profile_pic = "")
    {
        if ($profile_pic != "") {
            return $this->execute("UPDATE users SET name=?, email=?, phone=?, profile_pic=? WHERE id=?", "ssssi", [$name, $email, $phone, $profile_pic, $id]);
        }
        return $this->execute("UPDATE users SET name=?, email=?, phone=? WHERE id=?", "sssi", [$name, $email, $phone, $id]);
    }

    public function changePassword($user_id, $oldPassword, $newPassword)
    {
        $user = $this->getUser($user_id);
        if ($user != null && password_verify($oldPassword, $user['password_hash'])) {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            return $this->execute("UPDATE users SET password_hash=? WHERE id=?", "si", [$hash, $user_id]);
        }
        return false;
    }

    public function getAnnouncements($role)
    {
        $rows = $this->fetchAll("SELECT * FROM announcements ORDER BY published_at DESC");
        $list = [];

        foreach ($rows as $row) {
            if ($row['target_role'] == 'all' || $row['target_role'] == $role) {
                $author = $this->getUser($row['author_id']);
                $row['author'] = $author != null ? $author['name'] : '';
                $list[] = $row;
            }
        }

        return $list;
    }
}
