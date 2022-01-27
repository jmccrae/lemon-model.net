USE mysql;
UPDATE user SET Password=PASSWORD('lemonpass') WHERE user='root';
FLUSH PRIVILEGES;
