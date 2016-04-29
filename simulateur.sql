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

CREATE TABLE res_result(
  RES_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  RES_EVENT_ID INT NOT NULL,
  RES_DATE DATE NOT NULL,
  RES_LABEL VARCHAR(255) NOT NULL,
  RES_COMPETITION_ID INT NOT NULL
);

CREATE TABLE mar_market_result(
  MAR_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  MAR_FDJ_NUMBER INT NOT NULL,
  MAR_MARKET_TYPE VARCHAR(255) NOT NULL,
  MAR_RESULTAT CHAR(1) NOT NULL,
  MAR_RES_RESULT_ID INT NOT NULL,
  FOREIGN KEY(MAR_RES_RESULT_ID) REFERENCES res_result(RES_ID)
);

CREATE TABLE tea_team(
  TEA_ID INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
  TEA_LABEL VARCHAR(255) NOT NULL,
  TEA_COMPETITION_ID INT NOT NULL,
  TEA_POINTS INT NOT NULL,
  TEA_RANK INT NOT NULL,
  TEA_SERIE VARCHAR(255) NOT NULL
);

CREATE INDEX index_offer_id ON off_offer (OFF_ID);
CREATE INDEX index_offer_eventId ON off_offer (OFF_EVENT_ID);
CREATE INDEX index_outcome_id ON out_outcome (OUT_ID);
CREATE INDEX index_outcome_offer ON out_outcome (OUT_OFF_OFFER_ID);
CREATE INDEX index_result_id ON res_result (RES_ID);
CREATE INDEX index_result_event_id ON res_result (RES_EVENT_ID);
CREATE INDEX index_result_competition_id ON res_result (RES_COMPETITION_ID);
CREATE INDEX index_result_label ON res_result (RES_LABEL);
CREATE INDEX index_market_result_id ON mar_market_result (MAR_ID);
CREATE INDEX index_market_result_result ON mar_market_result (MAR_RES_RESULT_ID);
CREATE INDEX index_market_result_resultat ON mar_market_result (MAR_RESULTAT);
CREATE INDEX index_team_id ON tea_team (TEA_ID);
CREATE INDEX index_team_label ON tea_team (TEA_LABEL);
CREATE INDEX index_team_competition_id ON tea_team (TEA_COMPETITION_ID);
CREATE INDEX index_team_serie ON tea_team (TEA_SERIE);
CREATE INDEX index_team_points ON tea_team (TEA_POINTS);

