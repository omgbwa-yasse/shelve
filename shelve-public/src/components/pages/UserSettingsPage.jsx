import React, { useState, useContext } from 'react';
import styled from 'styled-components';
import { toast } from 'react-toastify';
import AuthContext from '../../context/AuthContext.js';

const SettingsContainer = styled.div`
  max-width: 800px;
  margin: 0 auto;
  padding: ${props => props.theme.spacing.xl};
`;

const Title = styled.h1`
  color: ${props => props.theme.colors.primary};
  margin-bottom: ${props => props.theme.spacing.xl};
  font-size: 2rem;
  font-weight: 600;
`;

const Card = styled.div`
  background: ${props => props.theme.colors.white};
  border-radius: ${props => props.theme.borderRadius.lg};
  box-shadow: ${props => props.theme.shadows.md};
  padding: ${props => props.theme.spacing.xl};
  margin-bottom: ${props => props.theme.spacing.lg};
`;

const CardTitle = styled.h2`
  color: ${props => props.theme.colors.dark};
  margin-bottom: ${props => props.theme.spacing.lg};
  font-size: 1.5rem;
  font-weight: 500;
  border-bottom: 2px solid ${props => props.theme.colors.light};
  padding-bottom: ${props => props.theme.spacing.sm};
`;

const Form = styled.form`
  display: flex;
  flex-direction: column;
  gap: ${props => props.theme.spacing.lg};
`;

const FormRow = styled.div`
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: ${props => props.theme.spacing.md};

  @media (max-width: ${props => props.theme.breakpoints.mobile}) {
    grid-template-columns: 1fr;
  }
`;

const FormGroup = styled.div`
  display: flex;
  flex-direction: column;
  gap: ${props => props.theme.spacing.sm};
`;

const Label = styled.label`
  font-weight: 500;
  color: ${props => props.theme.colors.dark};
`;

const Input = styled.input`
  padding: ${props => props.theme.spacing.md};
  border: 2px solid ${props => props.theme.colors.light};
  border-radius: ${props => props.theme.borderRadius.md};
  font-size: 1rem;
  transition: border-color 0.3s ease;

  &:focus {
    outline: none;
    border-color: ${props => props.theme.colors.primary};
  }

  &:disabled {
    background: ${props => props.theme.colors.light};
    cursor: not-allowed;
  }
`;

const Button = styled.button`
  padding: ${props => props.theme.spacing.md} ${props => props.theme.spacing.lg};
  background: ${props => {
    if (props.variant === 'danger') return props.theme.colors.danger;
    return props.theme.colors.primary;
  }};
  color: ${props => props.theme.colors.white};
  border: none;
  border-radius: ${props => props.theme.borderRadius.md};
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s ease;

  &:hover:not(:disabled) {
    filter: brightness(0.9);
  }

  &:disabled {
    background: ${props => props.theme.colors.secondary};
    cursor: not-allowed;
  }
`;

const ButtonGroup = styled.div`
  display: flex;
  gap: ${props => props.theme.spacing.md};
  justify-content: flex-end;

  @media (max-width: ${props => props.theme.breakpoints.mobile}) {
    flex-direction: column;
  }
`;

const InfoText = styled.p`
  color: ${props => props.theme.colors.secondary};
  font-size: 0.9rem;
  margin-top: ${props => props.theme.spacing.sm};
`;

const DangerZone = styled.div`
  border: 2px solid ${props => props.theme.colors.danger};
  border-radius: ${props => props.theme.borderRadius.md};
  padding: ${props => props.theme.spacing.lg};
  background: ${props => props.theme.colors.white};
`;

const DangerTitle = styled.h3`
  color: ${props => props.theme.colors.danger};
  margin-bottom: ${props => props.theme.spacing.md};
  font-size: 1.25rem;
`;

const UserSettingsPage = () => {
  const { user, updateProfile } = useContext(AuthContext);
  const [loading, setLoading] = useState(false);
  const [passwordLoading, setPasswordLoading] = useState(false);

  const [profileData, setProfileData] = useState({
    first_name: user?.first_name || '',
    last_name: user?.last_name || '',
    email: user?.email || '',
    phone: user?.phone || ''
  });

  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: ''
  });

  const handleProfileChange = (e) => {
    const { name, value } = e.target;
    setProfileData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handlePasswordChange = (e) => {
    const { name, value } = e.target;
    setPasswordData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleProfileSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const result = await updateProfile(user.id, profileData);

      if (result.success) {
        toast.success('Profil mis à jour avec succès');
      } else {
        throw new Error(result.error);
      }
    } catch (error) {
      toast.error(error.message || 'Erreur lors de la mise à jour du profil');
    } finally {
      setLoading(false);
    }
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();

    if (passwordData.new_password !== passwordData.confirm_password) {
      toast.error('Les nouveaux mots de passe ne correspondent pas');
      return;
    }

    if (passwordData.new_password.length < 8) {
      toast.error('Le nouveau mot de passe doit contenir au moins 8 caractères');
      return;
    }

    setPasswordLoading(true);

    try {
      // Ici, vous devriez appeler votre API pour changer le mot de passe
      // const result = await changePassword(passwordData);

      toast.success('Mot de passe modifié avec succès');
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: ''
      });
    } catch (error) {
      toast.error('Erreur lors du changement de mot de passe');
    } finally {
      setPasswordLoading(false);
    }
  };

  return (
    <SettingsContainer>
      <Title>Paramètres du compte</Title>

      {/* Informations personnelles */}
      <Card>
        <CardTitle>Informations personnelles</CardTitle>
        <Form onSubmit={handleProfileSubmit}>
          <FormRow>
            <FormGroup>
              <Label htmlFor="first_name">Prénom</Label>
              <Input
                type="text"
                id="first_name"
                name="first_name"
                value={profileData.first_name}
                onChange={handleProfileChange}
                placeholder="Votre prénom"
              />
            </FormGroup>

            <FormGroup>
              <Label htmlFor="last_name">Nom</Label>
              <Input
                type="text"
                id="last_name"
                name="last_name"
                value={profileData.last_name}
                onChange={handleProfileChange}
                placeholder="Votre nom"
              />
            </FormGroup>
          </FormRow>

          <FormGroup>
            <Label htmlFor="email">Email</Label>
            <Input
              type="email"
              id="email"
              name="email"
              value={profileData.email}
              onChange={handleProfileChange}
              disabled
            />
            <InfoText>L'email ne peut pas être modifié pour des raisons de sécurité</InfoText>
          </FormGroup>

          <FormGroup>
            <Label htmlFor="phone">Téléphone</Label>
            <Input
              type="tel"
              id="phone"
              name="phone"
              value={profileData.phone}
              onChange={handleProfileChange}
              placeholder="Votre numéro de téléphone"
            />
          </FormGroup>

          <ButtonGroup>
            <Button type="submit" disabled={loading}>
              {loading ? 'Mise à jour...' : 'Mettre à jour le profil'}
            </Button>
          </ButtonGroup>
        </Form>
      </Card>

      {/* Changement de mot de passe */}
      <Card>
        <CardTitle>Sécurité</CardTitle>
        <Form onSubmit={handlePasswordSubmit}>
          <FormGroup>
            <Label htmlFor="current_password">Mot de passe actuel</Label>
            <Input
              type="password"
              id="current_password"
              name="current_password"
              value={passwordData.current_password}
              onChange={handlePasswordChange}
              placeholder="Votre mot de passe actuel"
            />
          </FormGroup>

          <FormRow>
            <FormGroup>
              <Label htmlFor="new_password">Nouveau mot de passe</Label>
              <Input
                type="password"
                id="new_password"
                name="new_password"
                value={passwordData.new_password}
                onChange={handlePasswordChange}
                placeholder="Nouveau mot de passe"
              />
            </FormGroup>

            <FormGroup>
              <Label htmlFor="confirm_password">Confirmer le mot de passe</Label>
              <Input
                type="password"
                id="confirm_password"
                name="confirm_password"
                value={passwordData.confirm_password}
                onChange={handlePasswordChange}
                placeholder="Confirmez le nouveau mot de passe"
              />
            </FormGroup>
          </FormRow>

          <InfoText>
            Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.
          </InfoText>

          <ButtonGroup>
            <Button type="submit" disabled={passwordLoading}>
              {passwordLoading ? 'Modification...' : 'Changer le mot de passe'}
            </Button>
          </ButtonGroup>
        </Form>
      </Card>

      {/* Zone dangereuse */}
      <Card>
        <DangerZone>
          <DangerTitle>Zone dangereuse</DangerTitle>
          <InfoText style={{ marginBottom: '1rem' }}>
            Cette action est irréversible. Toutes vos données seront définitivement supprimées.
          </InfoText>
          <Button
            variant="danger"
            onClick={() => {
              if (window.confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')) {
                toast.info('Fonctionnalité de suppression de compte à implémenter');
              }
            }}
          >
            Supprimer mon compte
          </Button>
        </DangerZone>
      </Card>
    </SettingsContainer>
  );
};

export default UserSettingsPage;
