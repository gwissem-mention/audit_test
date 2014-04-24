UPDATE `hn_etablissement` SET `eta_codepostal` = CONCAT('0',`eta_codepostal`) WHERE length(`eta_codepostal`) = 4;
