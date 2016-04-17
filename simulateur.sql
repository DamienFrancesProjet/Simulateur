CREATE DATABASE IF NOT EXISTS simulateur;

USE simulateur;

CREATE TABLE off_offer(
  OFF_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  OFF_EVENT_ID INT NOT NULL,
  OFF_SPORT_ID INT NOT NULL,
  OFF_FDJ_NUMBER INT NOT NULL,
  OFF_MARKET_TYPE VARCHAR(255) NOT NULL,
  OFF_DATE DATE NOT NULL,
  OFF_LABEL VARCHAR(255) NOT NULL,
  OFF_COMPETITION_ID INT NOT NULL
);

CREATE TABLE out_outcome(
  OUT_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  OUT_COTE FLOAT NOT NULL,
  OUT_OFF_OFFER_ID INT NOT NULL,
  FOREIGN KEY(OUT_OFF_OFFER_ID) REFERENCES off_offer(OFF_ID)
);

CREATE INDEX index_offer_id ON off_offer (OFF_ID);
CREATE INDEX index_outcome_id ON out_outcome (OUT_ID);
CREATE INDEX index_outcome_offer ON out_outcome (OUT_OFF_OFFER_ID);
CREATE INDEX index_offer_eventId ON off_offer (OFF_EVENT_ID);
