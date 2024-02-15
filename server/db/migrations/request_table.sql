CREATE TABLE IF NOT EXISTS request
(
    id         INT PRIMARY KEY AUTO_INCREMENT,
    domain_id  INT,
    url_id     INT,
    element_id INT,
    time       DATETIME,
    duration   INT,
    count      INT,
    FOREIGN KEY (domain_id) REFERENCES domain (id),
    FOREIGN KEY (url_id) REFERENCES url (id),
    FOREIGN KEY (element_id) REFERENCES element (id)
);
